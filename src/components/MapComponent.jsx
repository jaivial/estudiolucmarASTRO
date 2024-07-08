import React, { useState, useRef, useEffect } from 'react';
import { MapContainer, TileLayer, FeatureGroup, Polygon } from 'react-leaflet';
import { EditControl } from 'react-leaflet-draw';
import 'leaflet-draw/dist/leaflet.draw.css';
import 'leaflet/dist/leaflet.css';
import axios from 'axios';
import { v4 as uuidv4 } from 'uuid';

const colors = ['red', 'green', 'blue', 'yellow', 'orange', 'purple', 'pink', 'brown', 'black', 'white', 'gray', 'cyan'];
const responsables = ['John Doe', 'Jane Smith', 'Alice Johnson', 'Bob Brown'];

const MapComponent = () => {
  const [center] = useState({ lat: 39.4033747, lng: -0.4028759 });
  const ZOOM_LEVEL = 16;
  const mapRef = useRef();
  const [zones, setZones] = useState([]);
  const [zoneData, setZoneData] = useState({ id: null, latlngs: [] });
  const [isPopupOpen, setIsPopupOpen] = useState(false);
  const [zone_name, setZoneName] = useState('');
  const [color, setColor] = useState(colors[0]);
  const [zone_responsable, setResponsable] = useState(responsables[0]);
  const featureGroupRef = useRef();

  useEffect(() => {
    fetchZones();
  }, []);

  const fetchZones = async () => {
    try {
      const response = await axios.get('http://localhost:8000/backend/zonas/fetchAllZones.php');
      console.log('Zones fetched:', response.data);
      setZones(response.data);
    } catch (error) {
      console.error('Error fetching zones:', error);
    }
  };

  const onCreated = (e) => {
    const { layerType, layer } = e;
    if (layerType === 'polygon') {
      const { _leaflet_id } = layer;
      const latlngs = layer.getLatLngs();
      setZoneData({ id: _leaflet_id, latlngs });
      setIsPopupOpen(true);
      layer.setStyle({ color });
      layer.bindPopup(document.createElement('div')).openPopup();
    }
  };

  const onDeleted = (e) => {
    console.log('Draw Deleted Event!', e);
    // Iterate through all layers in e.layers._layers
    for (const layerId in e.layers._layers) {
      if (e.layers._layers.hasOwnProperty(layerId)) {
        const layer = e.layers._layers[layerId];

        // Check if the layer has options and an id
        if (layer.options && layer.options.code_id) {
          console.log('Draw Deleted Event! ID:', layer.options.code_id);
          handleDelete(layer.options.code_id);
        }
      }
    }
  };

  const onEdited = (e) => {
    console.log('Draw Edited Event!', e);
    try {
      Object.values(e.layers._layers).forEach(async (layer) => {
        if (layer.options && layer.options.code_id) {
          const codeID = layer.options.code_id;
          const latlngs = layer.getLatLngs();

          // // Update the zones state with the new latlngs
          // setZones((prevZones) => prevZones.map((zone) => (zone.code_id === codeID ? { ...zone, latlngs } : zone)));

          // Call function to update the zone in the backend
          await updateZone(codeID, latlngs);
        }
      });
    } catch (error) {
      console.error('Error editing zone:', error);
    }
  };

  const updateZone = async (codeID, latlngs) => {
    try {
      const response = await axios.post('http://localhost:8000/backend/zonas/updateZone.php', { code_id: codeID, latlngs });
      console.log('Zone updated in backend:', response.data);
    } catch (error) {
      console.error('Error updating zone:', error);
      throw error; // Propagate the error for handling elsewhere if needed
    }
  };

  const handleDelete = async (zoneCodeId) => {
    try {
      const response = await axios.get(`http://localhost:8000/backend/zonas/deleteZone.php?zoneCodeId=${zoneCodeId}`);
      setZones((prevZones) => prevZones.filter((zone) => zone.code_id !== zoneCodeId));

      console.log('Zone deleted:', response.data);
    } catch (error) {
      console.error('Error deleting zone:', error);
    }
  };

  useEffect(() => {
    console.log('zones changed', zones);
    axios.get('http://localhost:8000/backend/zonas/checkInmuebleInZone.php').then((response) => {
      console.log('Inmueble data fetched:', response.data);
    });
  }, [zones]); // This will log whenever zones changes

  const handleSave = async () => {
    const code_id = uuidv4();
    const newZone = { code_id: code_id, zone_name: zone_name, color, zone_responsable, latlngs: zoneData.latlngs };
    try {
      const response = await axios.post('http://localhost:8000/backend/zonas/createNewZone.php', newZone);
      console.log('Zone saved:', response.data);
      // setZones([...zones, newZone]);
      const layer = featureGroupRef.current.getLayers().find((l) => l._leaflet_id === zoneData.id);
      if (layer) {
        layer.setStyle({ color });
        layer
          .bindPopup(
            `<div>
              <h3>${zone_name}</h3>
              <p>Responsable: ${zone_responsable}</p>
            </div>`,
          )
          .openPopup();
      }
      setZoneData({ id: null, latlngs: [] });
      setZoneName('');
      setColor(colors[0]);
      setResponsable(responsables[0]);
      setIsPopupOpen(false);
      fetchZones();
    } catch (error) {
      console.error('Error saving zone:', error);
    }
  };

  const handleZoneClick = (zone, layer) => {
    console.log('Zone clicked:', zone);

    if (layer) {
      layer
        .bindPopup(
          `
        <div class="flex flex-col items-center pt-2">
          <h3>Nombre: ${zone.zone_name}</h3>
          <p>Responsable: ${zone.zone_responsable}</p>
        </div>
      `,
        )
        .openPopup();
    }
  };

  return (
    <div className="w-full flex flex-col items-center justify-start h-dvh bg-red-100">
      <MapContainer center={center} zoom={ZOOM_LEVEL} ref={mapRef} className="w-full h-dvh z-0">
        <TileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" />
        <FeatureGroup ref={featureGroupRef}>
          {zones.map((zone) => (
            <Polygon
              key={zone.id}
              positions={zone.latlngs[0].map((coord) => [coord.lat, coord.lng])} // Accessing the first sub-array and mapping latlngs to [lat, lng] pairs
              color={zone.color}
              eventHandlers={{ click: (event) => handleZoneClick(zone, event.target) }}
              id={zone.id}
              code_id={zone.code_id}
            ></Polygon>
          ))}
          <EditControl
            position="topright"
            onCreated={onCreated}
            onDeleted={onDeleted}
            onEdited={onEdited}
            draw={{
              rectangle: false,
              polyline: false,
              circle: false,
              circlemarker: false,
              marker: false,
              polygon: true,
            }}
          />
        </FeatureGroup>
      </MapContainer>
      {isPopupOpen && (
        <div className="bg-slate-100 rounded-lg shadow-lg p-4 w-[60%] flex flex-col items-center gap-4 fixed top-[50%] left-[50%] translate-x-[-50%] translate-y-[-50%] z-30">
          <h2 class="font-sans text-center text-slate-800 font-bold text-lg">Editar zona</h2>
          <label class="flex flex-col items-center gap-1 w-[60%]">
            Nombre:
            <input class="font-sans text-base text-center border-2 border-slate-300 rounded-lg p-1 w-full bg-white" type="text" value={zone_name} onChange={(e) => setZoneName(e.target.value)} />
          </label>
          <label class="flex flex-col items-center gap-1 w-[60%]">
            Color:
            <select class="font-sans text-base  text-center border-2 border-slate-300 rounded-lg p-1 w-full bg-white" value={color} onChange={(e) => setColor(e.target.value)}>
              {colors.map((c) => (
                <option class="text-center" key={c} value={c}>
                  {c}
                </option>
              ))}
            </select>
          </label>
          <label class="flex flex-col items-center gap-1 w-[60%]">
            Responsable:
            <select class="font-sans text-base  text-center border-2 border-slate-300 rounded-lg p-1 w-full bg-white" value={zone_responsable} onChange={(e) => setResponsable(e.target.value)}>
              {responsables.map((r) => (
                <option key={r} value={r}>
                  {r}
                </option>
              ))}
            </select>
          </label>
          <button class="font-sans font-bld text-white py-2 px-4 text-center bg-emerald-700 rounded-lg" onClick={handleSave}>
            Guardar
          </button>
        </div>
      )}
    </div>
  );
};

export default MapComponent;
