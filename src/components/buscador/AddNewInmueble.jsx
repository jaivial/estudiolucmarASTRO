import React, { useState } from 'react';
import { MapContainer, TileLayer, Marker, useMapEvents } from 'react-leaflet';
import Select from 'react-select';
import axios from 'axios';
import 'leaflet/dist/leaflet.css';
import Toastify from 'toastify-js';
import 'react-toastify/dist/ReactToastify.css';

const icon = L.icon({
  iconUrl: 'https://cdn.jsdelivr.net/npm/leaflet@1.7.1/dist/images/marker-icon.png',
  iconSize: [25, 41],
  iconAnchor: [12, 41],
  popupAnchor: [1, -34],
  shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
  shadowSize: [41, 41],
  shadowAnchor: [12, 41],
});

// Get the current year
const currentYear = new Date().getFullYear();

// Generate years from 1850 to the current year
const years = Array.from({ length: currentYear - 1850 + 1 }, (_, index) => ({ value: 1850 + index, label: (1850 + index).toString() }));
const meters = Array.from({ length: 2501 }, (_, index) => ({ value: index, label: index.toString() + ' m²' }));

const AddInmueblePopup = ({ showAddNewInmueble, setShowAddNewInmueble, fetchData, currentPage, searchTerm, fetchParentsAndChilds, handleIconAddInmueble }) => {
  const [direccion, setDireccion] = useState('');
  const [tipo, setTipo] = useState('Selecciona un tipo');
  const [uso, setUso] = useState('Selecciona un uso');
  const [anoConstruccion, setAnoConstruccion] = useState(null);
  const [superficie, setSuperficie] = useState(null); // Ensure correct initial state
  const [categoriaOcupacion, setCategoriaOcupacion] = useState('Elige una opción');
  const [markerPosition, setMarkerPosition] = useState(null);

  const handleAddInmueble = () => {
    if (!direccion || tipo === 'Selecciona un tipo' || uso === 'Selecciona un uso' || !anoConstruccion || !superficie || categoriaOcupacion === 'Elige una opción' || !markerPosition) {
      Toastify({
        text: 'Debes rellenar todos los campos',
        duration: 2500,
        destination: 'https://github.com/apvarun/toastify-js',
        newWindow: true,
        close: false,
        gravity: 'top', // `top` or `bottom`
        position: 'center', // `left`, `center` or `right`
        stopOnFocus: true, // Prevents dismissing of toast on hover
        style: {
          borderRadius: '10px',
          backgroundImage: 'linear-gradient(to right top, #c62828, #b92125, #ac1a22, #a0131f, #930b1c)',
          textAlign: 'center',
        },
        onClick: function () {}, // Callback after click
      }).showToast();
      return;
    }

    const coordinatesArray = markerPosition ? [markerPosition.lat.toString(), markerPosition.lng.toString()] : [];
    axios
      .get('http://localhost:8000/backend/inmuebles/addNewInmueble.php', {
        params: {
          direccion,
          tipo,
          uso,
          anoConstruccion: anoConstruccion.value,
          superficie: superficie.value,
          categoriaOcupacion,
          coordinates: coordinatesArray,
        },
      })
      .then((response) => {
        console.log(response.data);
        if (response.data.status === 'success') {
          axios
            .get('http://localhost:8000/backend/zonas/checkInmuebleInZone.php')
            .then((response) => {
              console.log(response.data);
              if (response.data) {
                Toastify({
                  text: 'Inmueble agregado.',
                  duration: 2500,
                  destination: 'https://github.com/apvarun/toastify-js',
                  newWindow: true,
                  close: false,
                  gravity: 'top', // `top` or `bottom`
                  position: 'center', // `left`, `center` or `right`
                  stopOnFocus: true, // Prevents dismissing of toast on hover
                  style: {
                    borderRadius: '10px',
                    backgroundImage: 'linear-gradient(to right bottom, #00603c, #006f39, #007d31, #008b24, #069903)',
                    textAlign: 'center',
                  },
                  onClick: function () {}, // Callback after click
                }).showToast();
                setShowAddNewInmueble(false);
                fetchData(currentPage, searchTerm);
                fetchParentsAndChilds();
              }
            })
            .catch((error) => {
              console.error('Error fetching data:', error);
            });
        }
        // Handle success
      })
      .catch((error) => {
        console.error('There was an error!', error);
      });
  };

  const MapEvents = () => {
    useMapEvents({
      click(e) {
        setMarkerPosition(e.latlng);
      },
    });
    return null;
  };

  return (
    <div className={`popup-container fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-75 ${showAddNewInmueble ? '' : 'hidden'} z-50`}>
      <div className="popup-content bg-white p-4 shadow-lg flex flex-col justify-center items-center gap-4 rounded-lg w-4/6">
        <h2 className="text-lg font-bold w-[80%] text-center flex justify-center">Añadir Inmueble</h2>
        <div className="flex flex-col gap-4 w-full justify-center items-center text-center">
          <input type="text" placeholder="Dirección" value={direccion} onChange={(e) => setDireccion(e.target.value)} className="border p-2 rounded w-full" />
          <select value={tipo} onChange={(e) => setTipo(e.target.value)} className="border p-2 rounded w-full">
            <option>Selecciona un tipo</option>
            <option>Urbano</option>
            <option>Rural</option>
          </select>
          <select value={uso} onChange={(e) => setUso(e.target.value)} className="border p-2 rounded w-full">
            <option>Selecciona un uso</option>
            <option>Industrial</option>
            <option>Residencial</option>
            <option>Almacén-Estacionamiento</option>
            <option>Comercial</option>
            <option>Sanidad y Beneficiencia</option>
            <option>Suelo sin edif</option>
          </select>
          <Select maxMenuHeight={150} options={years} placeholder="Año de construcción" value={anoConstruccion} onChange={setAnoConstruccion} className="w-full z-[9999]" />
          <Select maxMenuHeight={150} options={meters} placeholder="Superfície m²" value={superficie} onChange={setSuperficie} className="w-full z-[9990]" />
          <select value={categoriaOcupacion} onChange={(e) => setCategoriaOcupacion(e.target.value)} className="border p-2 rounded w-full">
            <option>Elige una opción</option>
            <option>Propietario</option>
            <option>Inquilino</option>
            <option>Vacío</option>
          </select>
          <h2 className="text-lg font-bold w-[80%] text-center flex justify-center">Selecciona su ubicación</h2>
          <MapContainer center={[39.4033747, -0.4028759]} zoom={14} className="h-[200px] w-[100%]">
            <TileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" />
            {markerPosition && <Marker position={markerPosition} icon={icon} />}
            <MapEvents />
          </MapContainer>
          <div className="flex justify-center gap-4">
            <button className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" onClick={handleAddInmueble}>
              Añadir
            </button>
            <button className="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded" onClick={() => handleIconAddInmueble()}>
              Cerrar
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};

export default AddInmueblePopup;
