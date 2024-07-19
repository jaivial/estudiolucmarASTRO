import React, { useEffect, useState } from 'react';
import axios from 'axios';
import ItemDetails from './ItemDetails';
import LoadingScreen from '../loadingScreen';

const Table = () => {
  const [loading, setLoading] = useState(true);
  const [data, setData] = useState([]);
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [itemsPerPage] = useState(10);
  const [searchTerm, setSearchTerm] = useState('');
  const [childsEscalera, setChildsEscalera] = useState([]);
  const [childsEdificio, setChildsEdificio] = useState([]);
  const [parentsEscalera, setParentsEscalera] = useState([]);
  const [parentsEdificio, setParentsEdificio] = useState([]);
  const [expandedItems, setExpandedItems] = useState({});
  const [showExtraButtons, setShowExtraButtons] = useState(false);
  const [showUngroupButtons, setShowUngroupButtons] = useState(false);
  const [selectedItems, setSelectedItems] = useState(new Set());
  const [selectedItemsUngroup, setSelectedItemsUngroup] = useState(new Set());
  const [showPopup, setShowPopup] = useState(false);
  const [showPopupUngroup, setShowPopupUngroup] = useState(false);
  const [showFormType, setShowFormType] = useState(null); // New state to manage form type
  const [selectedType, setSelectedType] = useState('Edificio'); // For radio button selection in the assign to existing form
  const [formData, setFormData] = useState({
    tipo: '',
    nombre: '',
    existingGroup: '',
    grupo: '',
  });
  const [selectedId, setSelectedId] = useState(null);
  const [showAskForDeleteOrphan, setShowAskForDeleteOrphan] = useState(false);
  const [orphanInfo, setOrphanInfo] = useState([]);
  const [showDeleteInmuebleButtons, setShowDeleteInmuebleButtons] = useState(false);
  const [showAddInmuebleButtons, setShowAddInmuebleButtons] = useState(false);
  const [showPopupDeleteInmueble, setShowPopupDeleteInmueble] = useState(false);
  const [thereAreChildrenDelete, setThereAreChildrenDelete] = useState(false);
  const [keepChildren, setKeepChildren] = useState([]);
  const [parentData, setParentData] = useState([]);

  useEffect(() => {
    fetchData(currentPage, searchTerm);
  }, [currentPage, searchTerm]);

  useEffect(() => {
    fetchParentsAndChilds();
  }, []);

  const fetchData = async (page, term) => {
    try {
      const response = await axios.get('http://localhost:8000/backend/inmuebles/tabla.php', {
        params: {
          itemsPerPage,
          page,
          direccion: term,
        },
      });
      const result = response.data;
      setData(result.data || []);
      setCurrentPage(result.currentPage || 1);
      setTotalPages(result.totalPages || 1);
      setLoading(false);
    } catch (error) {
      console.error('Error fetching data:', error);
      setLoading(false);
    }
  };

  const fetchParentsAndChilds = async () => {
    try {
      const response = await axios.get('http://localhost:8000/backend/inmuebles/fetchAllInmuebles.php');
      const result = response.data;

      setChildsEscalera(result.childsescalera || []);
      setChildsEdificio(result.childsedificio || []);
      setParentsEscalera(result.parentsescalera || []);
      setParentsEdificio(result.parentsedificio || []);
    } catch (error) {
      console.error('Error fetching data:', error);
    }
  };

  const handlePrevious = () => {
    if (currentPage > 1) {
      setCurrentPage(currentPage - 1);
    }
  };

  const handleNext = () => {
    if (currentPage < totalPages) {
      setCurrentPage(currentPage + 1);
    }
  };

  const handleSearch = (e) => {
    e.preventDefault();
    setCurrentPage(1);
    fetchData(1, searchTerm);
  };

  const handleClearSearch = () => {
    setSearchTerm('');
    setCurrentPage(1);
    fetchData(1, '');
  };

  const handleToggle = (itemId) => {
    setExpandedItems((prev) => ({
      ...prev,
      [itemId]: !prev[itemId],
    }));
  };

  const handleCheckboxChange = (itemId) => {
    setSelectedItems((prev) => {
      const newSelectedItems = new Set(prev);
      newSelectedItems.has(itemId) ? newSelectedItems.delete(itemId) : newSelectedItems.add(itemId);
      return newSelectedItems;
    });
    console.log('selectedItems', selectedItems);
  };

  const handleCheckboxChangeUngroup = (itemId) => {
    setSelectedItemsUngroup((prev) => {
      const newSelectedItems = new Set(prev);
      newSelectedItems.has(itemId) ? newSelectedItems.delete(itemId) : newSelectedItems.add(itemId);
      return newSelectedItems;
    });
  };

  const handleIconClick = () => {
    setShowExtraButtons(!showExtraButtons);
    if (showUngroupButtons) setShowUngroupButtons(false);
    if (showDeleteInmuebleButtons) setShowDeleteInmuebleButtons(false);
    if (showAddInmuebleButtons) setShowAddInmuebleButtons(false);
    setSelectedItems(new Set());
    setSelectedItemsUngroup(new Set());
  };

  const handleIconClickUngroup = () => {
    setShowUngroupButtons(!showUngroupButtons);
    if (showExtraButtons) setShowExtraButtons(false);
    if (showDeleteInmuebleButtons) setShowDeleteInmuebleButtons(false);
    if (showAddInmuebleButtons) setShowAddInmuebleButtons(false);
    setSelectedItems(new Set());
    setSelectedItemsUngroup(new Set());
  };

  const handleIconDeleteInmueble = () => {
    setShowDeleteInmuebleButtons(!showDeleteInmuebleButtons);
    if (showExtraButtons) setShowExtraButtons(false);
    if (showUngroupButtons) setShowUngroupButtons(false);
    if (showAddInmuebleButtons) setShowAddInmuebleButtons(false);
    setSelectedItems(new Set());
    setSelectedItemsUngroup(new Set());
  };

  const handleIconAddInmueble = () => {
    setShowAddInmuebleButtons(!showAddInmuebleButtons);
    if (showExtraButtons) setShowExtraButtons(false);
    if (showUngroupButtons) setShowUngroupButtons(false);
    if (showDeleteInmuebleButtons) setShowDeleteInmuebleButtons(false);
    setSelectedItems(new Set());
    setSelectedItemsUngroup(new Set());
  };

  const handlePopupToggle = () => {
    setShowPopup(!showPopup);
    setShowFormType(null); // Reset form type when closing popup
  };

  const ressetSelectedType = () => {
    setSelectedType('');
  };

  useEffect(() => {
    ressetSelectedType();
  }, [showFormType]);

  const handlePopupToggleUngroup = () => {
    setShowPopupUngroup(!showPopupUngroup);
  };

  const handlePopupToggleDeleteInmueble = () => {
    // Example: Send DELETE request to backend
    console.log('Chcking children:', selectedItems);

    axios
      .get('http://localhost:8000/backend/inmuebles/checkChildrenDelete.php', {
        params: {
          inmuebles: Array.from(selectedItems),
        },
      })
      .then((response) => {
        console.log(response.data);
        if (response.data.status === 'success') {
          setKeepChildren(response.data.data);
          setParentData(response.data.parentdata);
          setThereAreChildrenDelete(true);
        } else {
          setThereAreChildrenDelete(false);
        }
      })
      .catch((error) => {
        console.error('Error deleting orphan:', error);
        alert('Error deleting orphan: ' + error.message);
      });

    setShowPopupDeleteInmueble(!showPopupDeleteInmueble);
  };

  const handleFormChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]: value,
    }));
    console.log(formData);
  };

  const handleSubmitForm = async (e) => {
    e.preventDefault();
    let url = '';
    let payload = '';
    if (showFormType === 'new' && formData.tipo === 'Edificio') {
      url = 'http://localhost:8000/backend/inmuebles/agruparNuevoEdificio.php';
      payload = {
        type: formData.tipo,
        name: formData.nombre,
        inmuebles: Array.from(selectedItems),
      };
    } else if (showFormType === 'new' && formData.tipo === 'Escalera') {
      url = 'http://localhost:8000/backend/inmuebles/agruparNuevaEscalera.php'; // URL for creating a new escalar
      payload = {
        // Payload for the POST request
        type: formData.tipo, // Type of building (Edificio or Escalera)
        name: formData.nombre, // Name of the building
        inmuebles: Array.from(selectedItems), // Array of selected items
        grupo: formData.grupo, // ID of the group to assign the building to
      }; // Payload for the POST request
    } else if (showFormType === 'existing' && formData.tipo === 'Edificio') {
      url = 'http://localhost:8000/backend/inmuebles/agruparExistenteEdificio.php'; // URL for creating a new escalar
      payload = {
        // Payload for the POST request
        type: formData.tipo, // Type of building (Edificio or Escalera)
        inmuebles: Array.from(selectedItems), // Array of selected items
        existingGroup: formData.existingGroup, // ID of the group to assign the building to
      }; // Payload for the POST request
    } else if (showFormType === 'existing' && formData.tipo === 'Escalera') {
      url = 'http://localhost:8000/backend/inmuebles/agruparExistenteEscalera.php'; // URL for creating a new escalar
      payload = {
        // Payload for the POST request
        type: formData.tipo, // Type of building (Edificio or Escalera)
        inmuebles: Array.from(selectedItems), // Array of selected items
        existingGroup: formData.existingGroup, // ID of the group to assign the building to
      }; // Payload for the POST request
    }

    try {
      await axios.post(url, payload);
      alert('Operation successful!');
      setShowPopup(false);
      setSelectedItems(new Set());
      setFormData({ tipo: '', nombre: '', existingGroup: '', grupo: '' });
      handleIconClick();
      fetchData(currentPage, searchTerm);
      fetchParentsAndChilds();
      setShowExtraButtons(false);
      setShowUngroupButtons(false);
    } catch (error) {
      console.error('Error performing operation:', error);
    }
  };

  const handleSubmitFormUngroup = async (e) => {
    e.preventDefault();
    const url = 'http://localhost:8000/backend/inmuebles/desagrupar.php';
    const payload = { inmuebles: Array.from(selectedItemsUngroup) };

    try {
      const response = await axios.post(url, payload);
      console.log(response.data);
      if (response.data.status === 'failure') {
        setShowAskForDeleteOrphan(true);
        setOrphanInfo(response.data.data);
      }

      alert('Operation successful!');
      setShowPopupUngroup(false);
      setSelectedItemsUngroup(new Set());
      fetchData(currentPage, searchTerm);
      fetchParentsAndChilds();
      setShowExtraButtons(false);
      setShowUngroupButtons(false);
    } catch (error) {
      console.error('Error performing operation:', error);
    }
  };

  const handleDeleteOrphan = () => {
    if (!orphanInfo || orphanInfo.length === 0) {
      console.error('Orphan info is empty or undefined');
      return;
    }

    console.log('Deleting orphan:', orphanInfo[0]);

    axios
      .get('http://localhost:8000/backend/inmuebles/deleteOrphan.php', {
        params: {
          id: orphanInfo[0].id,
        },
      })
      .then((response) => {
        console.log(response.data);
        if (response.data.status === 'success') {
          setShowAskForDeleteOrphan(false);
          fetchData(currentPage, searchTerm);
          fetchParentsAndChilds();
        } else {
          console.error('Error deleting orphan:', response.data.message);
          alert('Error deleting orphan: ' + response.data.message);
          setShowAskForDeleteOrphan(false);
        }
      })
      .catch((error) => {
        console.error('Error deleting orphan:', error);
        alert('Error deleting orphan: ' + error.message);
      });
  };

  const handleKeepOrphan = () => {
    // Implement logic to keep the orphan item
    setShowAskForDeleteOrphan(false);
    setOrphanInfo([]);
  };

  const handleKeepDeleteInmueble = () => {
    // Implement logic to keep the orphan item
    setShowPopupDeleteInmueble(false);
    setSelectedId(null);
  };

  const handleDeleteInmueble = () => {
    console.log('handleDeleteInmueble', Array.from(selectedItems));
    axios
      .get('http://localhost:8000/backend/inmuebles/deleteInmueble.php', {
        params: {
          inmuebles: Array.from(selectedItems),
        },
      })
      .then((response) => {
        console.log(response.data);
        if (response.data.status === 'success') {
          setShowPopupDeleteInmueble(false);
          fetchData(currentPage, searchTerm);
          fetchParentsAndChilds();
          setShowExtraButtons(false);
          setShowUngroupButtons(false);
          setSelectedItems(new Set());
          setKeepChildren(new Set());
          setParentData([]);
          setThereAreChildrenDelete(false);
        } else {
          console.error('Error deleting orphan:', response.data.message);
          alert('Error deleting orphan: ' + response.data.message);
          setShowPopupDeleteInmueble(false);
        }
      })
      .catch((error) => {
        console.error('Error deleting orphan:', error);
        alert('Error deleting orphan: ' + error.message);
      });
  };

  const handleDeleteKeepChildren = () => {
    console.log('handleDeleteKeepChildren', Array.from(keepChildren));
    console.log('selectedItems', selectedItems);
    console.log('parentData', Array.from(parentData));
    axios
      .get('http://localhost:8000/backend/inmuebles/deleteKeepChildren.php', {
        params: {
          inmuebles: Array.from(keepChildren),
          parentdata: parentData,
        },
      })
      .then((response) => {
        console.log(response.data);

        setShowPopupDeleteInmueble(false);
        fetchData(currentPage, searchTerm);
        fetchParentsAndChilds();
        setShowExtraButtons(false);
        setShowUngroupButtons(false);
        setSelectedItems(new Set());
        setKeepChildren(new Set());
      })
      .catch((error) => {
        console.error('Error deleting orphan:', error);
        alert('Error deleting orphan: ' + error.message);
      });
  };

  const handleItemClick = (id) => {
    setSelectedId(id);
  };

  const handleClose = () => {
    setSelectedId(null);
  };

  const findOrphansEdificio = (parentEdificios, childEdificios) => {
    const childAgrupacionIds = new Set(childEdificios.map((child) => child.AgrupacionID_Edificio));
    return parentEdificios.filter((parent) => !childAgrupacionIds.has(parent.AgrupacionID_Edificio));
  };

  const findOrphansEscalera = (parentEscaleras, childEscaleras) => {
    const childAgrupacionIds = new Set(childEscaleras.map((child) => child.AgrupacionID_Escalera));
    return parentEscaleras.filter((parent) => !childAgrupacionIds.has(parent.AgrupacionID_Escalera));
  };

  useEffect(() => {
    const fetchOrphans = async () => {
      const orphansEdificio = await findOrphansEdificio(parentsEdificio, childsEdificio);
      console.log('orphans edificio', orphansEdificio);
      const orphansEscalera = await findOrphansEscalera(parentsEscalera, childsEscalera);
      console.log('orphans escalera', orphansEscalera);
    };

    fetchOrphans();
  }, [parentsEdificio, childsEdificio, parentsEscalera, childsEscalera]);

  const getChildDetailsEscalera = (itemId) => {
    const matchingChilds = childsEscalera.filter((child) => child.AgrupacionID_Escalera == itemId);
    console.log('matchingChilds', matchingChilds);
    const AgrupacionID_Edificio = itemId;

    return matchingChilds.length > 0 ? (
      matchingChilds.map(
        (child) =>
          child.ChildEscalera == '1' &&
          child.ParentEscalera == null && (
            <div
              key={child.id}
              className={`relative border py-4 mb-6 rounded-xl shadow-xl flex items-center justify-center flex-row w-full ${child.dataUpdateTime === 'red' ? 'bg-red-100' : child.dataUpdateTime === 'yellow' ? 'bg-yellow-100' : 'bg-green-100'}`}
            >
              {showUngroupButtons && <input type="checkbox" checked={selectedItemsUngroup.has(child.id)} onChange={() => handleCheckboxChangeUngroup(child.id)} className="mr-4" />}
              {showExtraButtons && <input type="checkbox" checked={selectedItems.has(child.id)} onChange={() => handleCheckboxChange(child.id)} className="mr-4" />}
              {showDeleteInmuebleButtons && <input type="checkbox" checked={selectedItems.has(child.id)} onChange={() => handleCheckboxChange(child.id)} className="mr-4" />}
              <div className="flex flex-row justify-start items-center gap-1 w-[70%] py-2">
                <p className="w-[55%] text-center">
                  <strong>Dirección:</strong> <br /> {child.direccion}
                </p>
                <p className="text-center w-[32%]">
                  <strong>Zona:</strong> <br /> {child.zona ? child.zona : 'N/A'}
                </p>
              </div>
              <div className="flex flex-row justify-end items-center gap-3 w-[30%]">
                {child.noticiastate === '1' && (
                  <svg xmlns="http://www.w3.org/2000/svg" width="2.1em" height="2.1em" viewBox="0 0 24 24">
                    <path
                      fill="currentColor"
                      d="M10 7h4V5.615q0-.269-.173-.442T13.385 5h-2.77q-.269 0-.442.173T10 5.615zm8 15q-1.671 0-2.835-1.164Q14 19.67 14 18t1.165-2.835T18 14t2.836-1.165T22 18t-1.164 2.836T18 22M4.615 20q-.69 0-1.153-.462T3 18.384V8.616q0-.691.463-1.153T4.615 7H9V5.615q0-.69.463-1.153T10.616 4h2.769q.69 0 1.153.462T15 5.615V7h4.385q.69 0 1.152.463T21 8.616v4.198q-.683-.414-1.448-.614T18 12q-2.496 0-4.248 1.752T12 18q0 .506.086 1.009t.262.991zM18 20.423q.2 0 .33-.13t.132-.331t-.131-.331t-.331-.13t-.33.13t-.132.332t.131.33t.331.131m-.385-1.846h.77v-3h-.77z"
                    />
                  </svg>
                )}
                {child.encargoState === '1' && (
                  <svg xmlns="http://www.w3.org/2000/svg" width="2em" height="2em" viewBox="0 0 20 20">
                    <path
                      fill="currentColor"
                      d="M2 3a1 1 0 0 1 2 0h13a1 1 0 1 1 0 2H4v12.5a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3 3.5a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 .5.5v7a2.5 2.5 0 0 1-2.5 2.5h-7A2.5 2.5 0 0 1 5 13.5zm3 7a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-2.55a1 1 0 0 0-.336-.748L11.332 8.13a.5.5 0 0 0-.664 0L8.336 10.2a1 1 0 0 0-.336.75z"
                    />
                  </svg>
                )}
                <div onClick={() => handleItemClick(child.id)} className="cursor-pointer w-[20%] mr-4">
                  <svg xmlns="http://www.w3.org/2000/svg" width="2.1em" height="2.1em" viewBox="0 0 16 16" className="text-cyan-800 bg-white rounded-full hover:w-[2.5em] hover:h-[2.5em] hover:shadow-lg hover:text-cyan-600">
                    <path fill="currentColor" d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0m1.062 4.312a1 1 0 1 0-2 0v2.75h-2.75a1 1 0 0 0 0 2h2.75v2.75a1 1 0 1 0 2 0v-2.75h2.75a1 1 0 1 0 0-2h-2.75Z" />
                  </svg>
                </div>
              </div>
            </div>
          ),
      )
    ) : (
      <p>No hay detalles disponibles</p>
    );
  };
  const getChildDetailsEdificio = (itemId) => {
    const matchingChilds = childsEdificio.filter((child) => child.AgrupacionID_Edificio == itemId);
    console.log('matchingChilds', matchingChilds);
    const AgrupacionID_Edificio = itemId;

    return matchingChilds.length > 0 ? (
      matchingChilds.map((child) =>
        child.ChildEdificio == '1' && child.ParentEscalera == null ? (
          <div key={child.id} className={`relative border py-4 mb-6 rounded-xl shadow-xl flex items-center justify-center flex-row w-full ${child.dataUpdateTime === 'red' ? 'bg-red-100' : child.dataUpdateTime === 'yellow' ? 'bg-yellow-100' : 'bg-green-100'}`}>
            {showUngroupButtons && <input type="checkbox" checked={selectedItemsUngroup.has(child.id)} onChange={() => handleCheckboxChangeUngroup(child.id)} className="mr-4" />}
            {showExtraButtons && <input type="checkbox" checked={selectedItems.has(child.id)} onChange={() => handleCheckboxChange(child.id)} className="mr-4" />}
            {showDeleteInmuebleButtons && <input type="checkbox" checked={selectedItems.has(child.id)} onChange={() => handleCheckboxChange(child.id)} className="mr-4" />}
            <div className="flex flex-row justify-start items-center gap-1 w-[70%] py-2">
              <p className="w-[55%] text-center">
                <strong>Dirección:</strong> <br /> {child.direccion}
              </p>
              <p className="text-center w-[32%]">
                <strong>Zona:</strong> <br /> {child.zona ? child.zona : 'N/A'}
              </p>
            </div>
            <div className="flex flex-row justify-end items-center gap-3 w-[30%]">
              {child.noticiastate === '1' && (
                <svg xmlns="http://www.w3.org/2000/svg" width="2.1em" height="2.1em" viewBox="0 0 24 24">
                  <path
                    fill="currentColor"
                    d="M10 7h4V5.615q0-.269-.173-.442T13.385 5h-2.77q-.269 0-.442.173T10 5.615zm8 15q-1.671 0-2.835-1.164Q14 19.67 14 18t1.165-2.835T18 14t2.836-1.165T22 18t-1.164 2.836T18 22M4.615 20q-.69 0-1.153-.462T3 18.384V8.616q0-.691.463-1.153T4.615 7H9V5.615q0-.69.463-1.153T10.616 4h2.769q.69 0 1.153.462T15 5.615V7h4.385q.69 0 1.152.463T21 8.616v4.198q-.683-.414-1.448-.614T18 12q-2.496 0-4.248 1.752T12 18q0 .506.086 1.009t.262.991zM18 20.423q.2 0 .33-.13t.132-.331t-.131-.331t-.331-.13t-.33.13t-.132.332t.131.33t.331.131m-.385-1.846h.77v-3h-.77z"
                  />
                </svg>
              )}
              {child.encargoState === '1' && (
                <svg xmlns="http://www.w3.org/2000/svg" width="2em" height="2em" viewBox="0 0 20 20">
                  <path
                    fill="currentColor"
                    d="M2 3a1 1 0 0 1 2 0h13a1 1 0 1 1 0 2H4v12.5a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3 3.5a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 .5.5v7a2.5 2.5 0 0 1-2.5 2.5h-7A2.5 2.5 0 0 1 5 13.5zm3 7a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-2.55a1 1 0 0 0-.336-.748L11.332 8.13a.5.5 0 0 0-.664 0L8.336 10.2a1 1 0 0 0-.336.75z"
                  />
                </svg>
              )}
              <div onClick={() => handleItemClick(child.id)} className="cursor-pointer w-[20%] mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="2.1em" height="2.1em" viewBox="0 0 16 16" className="text-cyan-800 bg-white rounded-full hover:w-[2.5em] hover:h-[2.5em] hover:shadow-lg hover:text-cyan-600">
                  <path fill="currentColor" d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0m1.062 4.312a1 1 0 1 0-2 0v2.75h-2.75a1 1 0 0 0 0 2h2.75v2.75a1 1 0 1 0 2 0v-2.75h2.75a1 1 0 1 0 0-2h-2.75Z" />
                </svg>
              </div>
            </div>
          </div>
        ) : (
          child.ChildEdificio == '1' &&
          child.ParentEscalera == '1' && (
            <div
              key={child.id}
              className={`relative border py-4 mb-6 rounded-xl shadow-xl flex items-center flex-col w-full ${child.ParentEscalera == '1' ? 'bg-slate-100' : child.dataUpdateTime === 'red' ? 'bg-red-100' : child.dataUpdateTime === 'yellow' ? 'bg-yellow-100' : 'bg-green-100'}`}
            >
              {showDeleteInmuebleButtons && <input type="checkbox" checked={selectedItems.has(child.id)} onChange={() => handleCheckboxChange(child.id)} className="mr-4" />}
              <div className="flex flex-row justify-start items-center gap-2 w-full mb-4 cursor-pointer" onClick={() => handleToggle(child.id)}>
                <div className="flex flex-row justify-start items-center gap-2 w-[70%] py-2">
                  <p className="w-[60%] text-center">
                    <strong>Dirección:</strong> <br /> {child.direccion}
                  </p>
                  <p className="text-center w-[40%]">
                    <strong>Zona:</strong> <br /> {child.zona ? child.zona : 'N/A'}
                  </p>
                  <p className="text-center w-[40%]">
                    <strong>Tipo:</strong> <br /> {child.TipoAgrupacion}
                  </p>
                </div>
                <div className="cursor-pointer flex flex-row justify-center w-[30%]">
                  <svg xmlns="http://www.w3.org/2000/svg" width="2.5em" height="2.5em" viewBox="0 0 24 24">
                    <path fill="currentColor" fillRule="evenodd" d="M7 9a1 1 0 0 0-.707 1.707l5 5a1 1 0 0 0 1.414 0l5-5A1 1 0 0 0 17 9z" clipRule="evenodd" />
                  </svg>
                </div>
              </div>
              {expandedItems[child.id] && <div className="w-full flex flex-col justify-center items-center px-2">{getChildDetailsEscalera(child.id)}</div>}
            </div>
          )
        ),
      )
    ) : (
      <p>No hay detalles disponibles</p>
    );
  };

  if (loading) {
    return <LoadingScreen />;
  }

  return (
    <div>
      {selectedId ? (
        <ItemDetails id={selectedId} onClose={handleClose} />
      ) : (
        <div className="container mx-auto p-4 pb-24 pt-8">
          <h1 className="text-3xl font-bold text-center font-sans w-full">Buscador de inmuebles</h1>
          <form onSubmit={handleSearch} className="mb-4 flex flex-col gap-4 mt-8 w-full justify-center items-center">
            <input type="text" value={searchTerm} onChange={(e) => setSearchTerm(e.target.value)} placeholder="Buscar dirección..." className="border border-gray-300 p-2 rounded w-[70%]" />
            <div className="flex gap-4 justify-center items-center">
              <button type="submit" className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-[100px]">
                Buscar
              </button>
              <button type="button" onClick={handleClearSearch} className="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded w-[100px]">
                Limpiar
              </button>
            </div>
          </form>
          <div className="flex flex-row gap-4 pt-2 pb-2 w-full justify-between">
            <div className="flex flex-row gap-4">
              <button type="button" onClick={handleIconClick} className={`flex items-center justify-center p-2 rounded shadow-lg hover:bg-blue-950 hover:text-white w-fit ${showExtraButtons ? 'bg-blue-950 text-white' : 'bg-blue-300 text-black'}`}>
                <svg xmlns="http://www.w3.org/2000/svg" width="2em" height="2em" viewBox="0 0 24 24">
                  <path fill="currentColor" d="M2 6H1l4-4l4 4H8v3H6V6H4v3H2zm11 4.9l1.3 1.1H16V9h2v3h3V8h1l-5-5l-5 5h1zm.8 11.1c-.5-.9-.8-1.9-.8-3c0-1.6.6-3.1 1.7-4.1L9 10l-7 6h2v6h3v-5h4v5zm4.2-7v3h-3v2h3v3h2v-3h3v-2h-3v-3z" />
                </svg>
              </button>
              <button
                type="button"
                onClick={handleIconClickUngroup}
                className={`flex items-center justify-center p-2 rounded shadow-lg bg-blue-300 hover:bg-blue-950 hover:text-white w-fit ${showUngroupButtons ? 'bg-blue-950 text-white' : 'bg-blue-300 text-black'}`}
              >
                <svg xmlns="http://www.w3.org/2000/svg" width="2em" height="2em" viewBox="0 0 24 24">
                  <path fill="currentColor" d="M2 6H1l4-4l4 4H8v3H6V6H4v3H2zm11 4.9l1.3 1.1H16V9h2v3h3V8h1l-5-5l-5 5h1zm.8 11.1c-.5-.9-.8-1.9-.8-3c0-1.6.6-3.1 1.7-4.1L9 10l-7 6h2v6h3v-5h4v5zm1.2-4v2h8v-2z" />
                </svg>
              </button>
            </div>
            <div className="flex flex-row gap-4">
              <button
                type="button"
                onClick={handleIconDeleteInmueble}
                className={`flex items-center justify-center p-2 rounded shadow-lg bg-blue-300 hover:bg-blue-950 hover:text-white w-fit ${showDeleteInmuebleButtons ? 'bg-blue-950 text-white' : 'bg-blue-300 text-black'}`}
              >
                <svg xmlns="http://www.w3.org/2000/svg" width="2em" height="2em" viewBox="0 0 16 16">
                  <g fill="currentColor">
                    <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L8 2.207l6.646 6.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293z" />
                    <path d="m8 3.293l4.712 4.712A4.5 4.5 0 0 0 8.758 15H3.5A1.5 1.5 0 0 1 2 13.5V9.293z" />
                    <path d="M12.5 16a3.5 3.5 0 1 0 0-7a3.5 3.5 0 0 0 0 7M11 12h3a.5.5 0 0 1 0 1h-3a.5.5 0 1 1 0-1" />
                  </g>
                </svg>
              </button>
              <button
                type="button"
                onClick={handleIconAddInmueble}
                className={`flex items-center justify-center p-2 rounded shadow-lg bg-blue-300 hover:bg-blue-950 hover:text-white w-fit ${showAddInmuebleButtons ? 'bg-blue-950 text-white' : 'bg-blue-300 text-black'}`}
              >
                <svg xmlns="http://www.w3.org/2000/svg" width="2em" height="2em" viewBox="0 0 16 16">
                  <g fill="currentColor">
                    <path d="M12.5 16a3.5 3.5 0 1 0 0-7a3.5 3.5 0 0 0 0 7m.5-5v1h1a.5.5 0 0 1 0 1h-1v1a.5.5 0 1 1-1 0v-1h-1a.5.5 0 1 1 0-1h1v-1a.5.5 0 0 1 1 0" />
                    <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L8 2.207l6.646 6.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293z" />
                    <path d="m8 3.293l4.712 4.712A4.5 4.5 0 0 0 8.758 15H3.5A1.5 1.5 0 0 1 2 13.5V9.293z" />
                  </g>
                </svg>
              </button>
            </div>
          </div>
          {showExtraButtons && (
            <div className="flex gap-4 mt-4 pb-4 w-full justify-center">
              <button type="button" onClick={handlePopupToggle} className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Agrupar
              </button>
              <button type="button" onClick={() => setShowExtraButtons(false)} className="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                Cerrar
              </button>
            </div>
          )}
          {showUngroupButtons && (
            <div className="flex gap-4 mt-4 pb-4 w-full justify-center">
              <button type="button" onClick={handlePopupToggleUngroup} className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Desagrupar
              </button>
              <button type="button" onClick={() => setShowUngroupButtons(false)} className="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                Cerrar
              </button>
            </div>
          )}
          {showDeleteInmuebleButtons && (
            <div className="flex gap-4 mt-4 pb-4 w-full justify-center">
              <button type="button" onClick={handlePopupToggleDeleteInmueble} className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Eliminar
              </button>
              <button type="button" onClick={() => setShowDeleteInmuebleButtons(false)} className="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                Cerrar
              </button>
            </div>
          )}
          {showAddInmuebleButtons && (
            <div className="flex gap-4 mt-4 pb-4 w-full justify-center">
              <button type="button" onClick={handlePopupToggleUngroup} className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Añadir
              </button>
              <button type="button" onClick={() => setShowAddInmuebleButtons(false)} className="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                Cerrar
              </button>
            </div>
          )}
          <div className="flex flex-col gap-4">
            {Array.isArray(data) && data.length > 0 ? (
              data.map((item) =>
                item.ParentEdificio === null && item.ParentEscalera === null ? (
                  <div
                    key={item.id}
                    className={`relative border px-2 py-4 mb-4 rounded-xl shadow-xl flex items-center flex-row w-full ${
                      item.AgrupacionParent === '1' ? 'bg-slate-100' : item.dataUpdateTime === 'red' ? 'bg-red-100' : item.dataUpdateTime === 'yellow' ? 'bg-yellow-100' : 'bg-green-100'
                    }`}
                  >
                    <div className="flex flex-row justify-between w-full">
                      {showExtraButtons && <input type="checkbox" checked={selectedItems.has(item.id)} onChange={() => handleCheckboxChange(item.id)} className="mr-4" />}
                      {showDeleteInmuebleButtons && <input type="checkbox" checked={selectedItems.has(item.id)} onChange={() => handleCheckboxChange(item.id)} className="mr-4" />}
                      <div className="flex flex-row justify-start items-center gap-1 w-[70%] py-2">
                        <p className="w-[55%] text-center">
                          <strong>Dirección:</strong> <br /> {item.direccion}
                        </p>
                        <p className="text-center w-[32%]">
                          <strong>Zona:</strong> <br /> {item.zona ? item.zona : 'N/A'}
                        </p>
                      </div>
                      <div className="flex flex-row justify-end items-center gap-3 w-[30%]">
                        <div className="flex flex-row gap-2 mr-4">
                          {item.noticiastate === '1' && (
                            <svg xmlns="http://www.w3.org/2000/svg" width="2.1em" height="2.1em" viewBox="0 0 24 24">
                              <path
                                fill="currentColor"
                                d="M10 7h4V5.615q0-.269-.173-.442T13.385 5h-2.77q-.269 0-.442.173T10 5.615zm8 15q-1.671 0-2.835-1.164Q14 19.67 14 18t1.165-2.835T18 14t2.836 1.165T22 18t-1.164 2.836T18 22M4.615 20q-.69 0-1.153-.462T3 18.384V8.616q0-.691.463-1.153T4.615 7H9V5.615q0-.69.463-1.153T10.616 4h2.769q.69 0 1.153.462T15 5.615V7h4.385q.69 0 1.152.463T21 8.616v4.198q-.683-.414-1.448-.614T18 12q-2.496 0-4.248 1.752T12 18q0 .506.086 1.009t.262.991zM18 20.423q.2 0 .33-.13t.132-.331t-.131-.331T18 19.5t-.33.13t-.132.332t.131.33t.331.131m-.385-1.846h.77v-3h-.77z"
                              />
                            </svg>
                          )}
                          {item.encargoState === '1' && (
                            <svg xmlns="http://www.w3.org/2000/svg" width="2em" height="2em" viewBox="0 0 20 20">
                              <path
                                fill="currentColor"
                                d="M2 3a1 1 0 0 1 2 0h13a1 1 0 1 1 0 2H4v12.5a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3 3.5a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 .5.5v7a2.5 2.5 0 0 1-2.5 2.5h-7A2.5 2.5 0 0 1 5 13.5zm3 7a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-2.55a1 1 0 0 0-.336-.748L11.332 8.13a.5.5 0 0 0-.664 0L8.336 10.2a1 1 0 0 0-.336.75z"
                              />
                            </svg>
                          )}
                        </div>
                        <div onClick={() => handleItemClick(item.id)} className="cursor-pointer w-[20%] mr-4">
                          <svg xmlns="http://www.w3.org/2000/svg" width="2.1em" height="2.1em" viewBox="0 0 16 16" className="text-cyan-800 bg-white rounded-full hover:w-[2.5em] hover:h-[2.5em] hover:shadow-lg hover:text-cyan-600">
                            <path fill="currentColor" d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0m1.062 4.312a1 1 0 1 0-2 0v2.75h-2.75a1 1 0 0 0 0 2h2.75v2.75a1 1 0 1 0 2 0v-2.75h2.75a1 1 0 1 0 0-2h-2.75Z" />
                          </svg>
                        </div>
                      </div>
                    </div>
                  </div>
                ) : (
                  item.ParentEdificio == '1' && (
                    <div
                      key={item.id}
                      className={`relative border px-2 py-4 mb-4 rounded-xl shadow-xl flex items-center flex-row w-full ${
                        item.ParentEdificio == '1' ? 'bg-slate-100' : item.dataUpdateTime === 'red' ? 'bg-red-100' : item.dataUpdateTime === 'yellow' ? 'bg-yellow-100' : 'bg-green-100'
                      }`}
                    >
                      {showDeleteInmuebleButtons && <input type="checkbox" checked={selectedItems.has(item.id)} onChange={() => handleCheckboxChange(item.id)} className="mr-4" />}

                      <div className="w-full flex flex-col justify-center items-center">
                        <div className="flex flex-row justify-start items-center gap-2 w-full mb-4 cursor-pointer" onClick={() => handleToggle(item.id)}>
                          <div className="flex flex-row justify-start items-center gap-2 w-[70%] py-2">
                            <p className="w-[60%] text-center">
                              <strong>Dirección:</strong> <br /> {item.direccion}
                            </p>
                            <p className="text-center w-[40%]">
                              <strong>Zona:</strong> <br /> {item.zona ? item.zona : 'N/A'}
                            </p>
                            <p className="text-center w-[40%]">
                              <strong>Tipo:</strong> <br /> {item.TipoAgrupacion}
                            </p>
                          </div>
                          <div className="cursor-pointer flex flex-row justify-center w-[30%]">
                            <svg xmlns="http://www.w3.org/2000/svg" width="2.5em" height="2.5em" viewBox="0 0 24 24">
                              <path fill="currentColor" fillRule="evenodd" d="M7 9a1 1 0 0 0-.707 1.707l5 5a1 1 0 0 0 1.414 0l5-5A1 1 0 0 0 17 9z" clipRule="evenodd" />
                            </svg>
                          </div>
                        </div>
                        {console.log('AgrupacionID_Edificio', item.AgrupacionID_Edificio)}
                        {expandedItems[item.id] && <div className="w-full">{getChildDetailsEdificio(item.AgrupacionID_Edificio)}</div>}
                      </div>
                    </div>
                  )
                ),
              )
            ) : (
              <p>No hay datos disponibles</p>
            )}
          </div>
          <div className="flex mt-4 w-full flex-row items-center justify-center">
            <div className="flex flex-row justify-center items-center gap-3">
              {/* Previous Button */}
              <button type="button" onClick={handlePrevious} disabled={currentPage === 1} className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-[100px]">
                Previous
              </button>

              {/* Page Count Display */}
              <div className="text-gray-700 font-semibold">
                Página {currentPage} de {totalPages}
              </div>

              {/* Next Button */}
              <button type="button" onClick={handleNext} disabled={currentPage === totalPages} className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-[100px]">
                Next
              </button>
            </div>
          </div>

          {showPopup && (
            <div className="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-75">
              <div className="bg-white p-6 rounded shadow-lg">
                {!showFormType && (
                  <>
                    <button onClick={() => setShowFormType('new')} className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-4">
                      Crear nuevo grupo
                    </button>
                    <button onClick={() => setShowFormType('existing')} className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-4">
                      Asignar a grupo existente
                    </button>
                    <button type="button" onClick={handlePopupToggle} className="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                      Cerrar
                    </button>
                  </>
                )}
                {showFormType === 'new' && (
                  <div className="relative pt-0 flex flex-col justify-center items-center">
                    <div className="absolute top-0 -left-2 p-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-3 rounded" onClick={() => setShowFormType('')}>
                      <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M8.707 7.707L13.414 3l-4.707-4.707L7.293 3H3v4.293" />
                      </svg>
                    </div>
                    <h2 className="text-lg font-bold mb-4 w-[60%] text-center flex justify-center">Crear nuevo grupo</h2>
                    <form onSubmit={handleSubmitForm} className="flex flex-col gap-4">
                      <div>
                        <label className="block mb-2">Tipo:</label>
                        <div className="flex gap-4">
                          <label>
                            <input type="radio" name="tipo" value="Edificio" checked={formData.tipo === 'Edificio'} onChange={handleFormChange} />
                            Edificio
                          </label>
                          <label>
                            <input type="radio" name="tipo" value="Escalera" checked={formData.tipo === 'Escalera'} onChange={handleFormChange} />
                            Escalera
                          </label>
                        </div>
                      </div>
                      {formData.tipo === 'Edificio' ? (
                        <div>
                          <div>
                            <label className="block mb-2">Nombre:</label>
                            <input type="text" name="nombre" value={formData.nombre} onChange={handleFormChange} className="border border-gray-300 p-2 rounded w-full" />
                          </div>
                        </div>
                      ) : formData.tipo === 'Escalera' ? (
                        <div>
                          <div>
                            <label className="block mb-2">Nombre:</label>
                            <input type="text" name="nombre" value={formData.nombre} onChange={handleFormChange} className="border border-gray-300 p-2 rounded w-full" />
                          </div>
                          <p>Debes seleccionar un grupo de pisos para crear una escalera</p>
                          <label className="block mb-2">Grupo:</label>
                          <select name="grupo" value={formData.grupo} onChange={handleFormChange} className="border border-gray-300 p-2 rounded w-full">
                            <option value="">Seleccione un grupo</option>
                            {parentsEdificio.map((parent) => (
                              <option key={parent.id} value={parent.AgrupacionID_Edificio}>
                                {parent.direccion} {parent.AgrupacionID_Edificio}
                              </option>
                            ))}
                          </select>
                        </div>
                      ) : (
                        <div>
                          <div>
                            <label className="block mb-2">Nombre:</label>
                            <input type="text" name="nombre" value={formData.nombre} onChange={handleFormChange} className="border border-gray-300 p-2 rounded w-full" />
                          </div>
                        </div>
                      )}
                      <div className="flex gap-4 mt-4">
                        <button type="button" onClick={handlePopupToggle} className="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                          Cerrar
                        </button>
                        <button type="submit" className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                          Crear
                        </button>
                      </div>
                    </form>
                  </div>
                )}
                {showFormType === 'existing' && (
                  <div className="relative pt-0 flex flex-col justify-center items-center">
                    <div className="absolute top-0 -left-2 p-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-3 rounded" onClick={() => setShowFormType('')}>
                      <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M8.707 7.707L13.414 3l-4.707-4.707L7.293 3H3v4.293" />
                      </svg>
                    </div>
                    <h2 className="text-lg font-bold mb-4 w-[60%] text-center flex justify-center">Asignar a grupo existente</h2>
                    <form onSubmit={handleSubmitForm} className="flex flex-col gap-4">
                      <div>
                        <label className="block mb-2">Tipo:</label>
                        <div className="flex gap-4">
                          <label>
                            <input
                              type="radio"
                              name="tipo"
                              value="Edificio"
                              checked={selectedType === 'Edificio'}
                              onChange={(e) => {
                                setSelectedType(e.target.value);
                                handleFormChange(e);
                              }}
                            />
                            Edificio
                          </label>
                          <label>
                            <input
                              type="radio"
                              name="tipo"
                              value="Escalera"
                              checked={selectedType === 'Escalera'}
                              onChange={(e) => {
                                setSelectedType(e.target.value);
                                handleFormChange(e);
                              }}
                            />
                            Escalera
                          </label>
                        </div>
                      </div>
                      <div>
                        <label className="block mb-2">Elige un grupo:</label>
                        <select name="existingGroup" value={formData.existingGroup} onChange={handleFormChange} className="border border-gray-300 p-2 rounded w-full">
                          <option value="">Seleccione un grupo</option>
                          {selectedType === 'Edificio'
                            ? parentsEdificio.map((parent) => (
                                <option key={parent.id} value={parent.AgrupacionID_Edificio}>
                                  {parent.direccion}
                                </option>
                              ))
                            : parentsEscalera.map((parent) => (
                                <option key={parent.id} value={parent.AgrupacionID_Escalera}>
                                  {parent.direccion}
                                </option>
                              ))}
                        </select>
                      </div>
                      <div className="flex gap-4 mt-4">
                        <button type="button" onClick={handlePopupToggle} className="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                          Cerrar
                        </button>
                        <button type="submit" className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                          Asignar
                        </button>
                      </div>
                    </form>
                  </div>
                )}
              </div>
            </div>
          )}

          {showPopupUngroup && (
            <div className="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-75">
              <div className="bg-white p-6 rounded shadow-lg">
                <div className="relative pt-0 flex flex-col justify-center items-center">
                  <div className="absolute top-0 -left-2 p-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-3 rounded" onClick={handlePopupToggleUngroup}>
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                      <path fill="currentColor" d="M8.707 7.707L13.414 3l-4.707-4.707L7.293 3H3v4.293" />
                    </svg>
                  </div>
                  <h2 className="text-lg font-bold mb-4 w-[60%] text-center flex justify-center">Desagrupar</h2>
                  <form onSubmit={handleSubmitFormUngroup} className="flex flex-col justify-center items-center gap-4">
                    <div className="flex flex-col justify-center items-center gap-4 w-[70%]">
                      <h2 className="text-center">Se van a desagrupar los elementos seleccionados.</h2>
                      <h2>¿Está seguro?</h2>
                      <div className="flex flex-row justify-center items-center gap-4">
                        <button type="button" onClick={handlePopupToggleUngroup} className="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                          Cancelar
                        </button>
                        <button type="submit" className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                          Desagrupar
                        </button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          )}
        </div>
      )}
      {showAskForDeleteOrphan && (
        <div className="popup-container fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-75">
          <div className="popup-content bg-white p-4 shadow-lg flex flex-col justify-center items-center gap-4 rounded-lg w-4/6">
            <h2 className="text-lg font-bold w-[80%] text-center flex justify-center">El siguiente grupo se ha quedado vacío:</h2>
            <p>{`${orphanInfo[0].direccion}`}</p>
            <p>¿Desea eliminarlo?</p>
            <div className="flex justify-center gap-4">
              <button className="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded" onClick={handleDeleteOrphan}>
                Eliminar
              </button>
              <button className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" onClick={handleKeepOrphan}>
                Mantener
              </button>
            </div>
          </div>
        </div>
      )}
      {showPopupDeleteInmueble && (
        <div className="popup-container fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-75">
          <div className="popup-content bg-white p-4 shadow-lg flex flex-col justify-center items-center gap-4 rounded-lg w-4/6">
            <h2 className="text-lg font-bold w-[80%] text-center flex justify-center">Eliminar elemento</h2>
            {thereAreChildrenDelete ? (
              <div className="flex flex-col gap-4 w-fit justify-center items-center">
                <p className="text-center w-full">Alguno de los elementos seleccionados contiene elementos agrupados.</p>
                <p className="text-center w-full">¿Desea eliminar los elementos agrupados o mantenerlos?</p>
                <div className="flex flex-row justify-center items-center gap-4">
                  <button className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" onClick={handleDeleteInmueble}>
                    Eliminar
                  </button>
                  <button className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" onClick={handleDeleteKeepChildren}>
                    Mantener
                  </button>
                </div>
                <button className="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded" onClick={handleKeepDeleteInmueble}>
                  Cancelar
                </button>
              </div>
            ) : (
              <div className="flex flex-col gap-4 w-full justify-center items-center text-center">
                <p>¿Está seguro?</p>
                <div className="flex justify-center gap-4">
                  <button className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" onClick={handleDeleteInmueble}>
                    Eliminar
                  </button>
                  <button className="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded" onClick={handleKeepDeleteInmueble}>
                    Cancelar
                  </button>
                </div>
              </div>
            )}
          </div>
        </div>
      )}
    </div>
  );
};

export default Table;
