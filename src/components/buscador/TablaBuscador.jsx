import React, { useEffect, useState } from 'react';
import axios from 'axios';

const Table = () => {
  const [data, setData] = useState([]);
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [itemsPerPage] = useState(10);
  const [searchTerm, setSearchTerm] = useState('');
  const [childs, setChilds] = useState([]);
  const [parents, setParents] = useState([]);
  const [expandedItems, setExpandedItems] = useState({});
  const [showExtraButtons, setShowExtraButtons] = useState(false);
  const [selectedItems, setSelectedItems] = useState(new Set());
  const [showPopup, setShowPopup] = useState(false);
  const [showFormType, setShowFormType] = useState(null); // New state to manage form type
  const [selectedType, setSelectedType] = useState('Edificio'); // For radio button selection in the assign to existing form
  const [formData, setFormData] = useState({
    tipo: '',
    nombre: '',
    numero: '',
    existingGroup: '',
  });

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
    } catch (error) {
      console.error('Error fetching data:', error);
    }
  };

  const fetchParentsAndChilds = async () => {
    try {
      const response = await axios.get('http://localhost:8000/backend/inmuebles/fetchAllInmuebles.php');
      const result = response.data;
      setChilds(result.childs || []);
      setParents(result.parents || []);
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
      if (newSelectedItems.has(itemId)) {
        newSelectedItems.delete(itemId);
      } else {
        newSelectedItems.add(itemId);
      }
      return newSelectedItems;
    });
  };

  const handleIconClick = () => {
    setShowExtraButtons(!showExtraButtons);
  };

  const handlePopupToggle = () => {
    setShowPopup(!showPopup);
    setShowFormType(null); // Reset form type when closing popup
  };

  const handleFormChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]: value,
    }));
    console.log(formData);
    console.log(selectedItems);
  };

  const handleSubmitForm = async (e) => {
    e.preventDefault();
    const url = showFormType === 'new' ? 'http://localhost:8000/backend/inmuebles/agrupar.php' : 'http://localhost:8000/backend/inmuebles/asignarGrupoExistente.php';

    const payload =
      showFormType === 'new'
        ? {
            type: formData.tipo,
            name: formData.nombre,
            number: formData.numero,
            inmuebles: Array.from(selectedItems),
          }
        : {
            tipo: formData.tipo,
            existingGroup: formData.existingGroup,
            selectedItems: Array.from(selectedItems),
          };

    try {
      await axios.post(url, payload);
      alert('Operation successful!');
      setShowPopup(false);
      setSelectedItems(new Set());
      fetchData(currentPage, searchTerm);
      fetchParentsAndChilds();
    } catch (error) {
      console.error('Error performing operation:', error);
    }
  };

  const getChildDetails = (itemId) => {
    const matchingChilds = childs.filter((child) => child.AgrupacionID == itemId);
    return matchingChilds.length > 0 ? (
      matchingChilds.map((child) => (
        <div key={child.id} className="item-card border mb-4 rounded shadow w-full">
          {showExtraButtons && <input type="checkbox" checked={selectedItems.has(child.id)} onChange={() => handleCheckboxChange(child.id)} className="mr-4" />}
          <p>
            <strong>Dirección:</strong> {child.direccion} {child.Numero}
          </p>
          <p>
            <strong>Zona:</strong> {child.zona}
          </p>
          <p>
            <strong>Estado de Encargo:</strong> {child.encargoState}
          </p>
          <p>
            <strong>Estado de Noticia:</strong> {child.noticiastate}
          </p>
        </div>
      ))
    ) : (
      <p>No child details available</p>
    );
  };

  return (
    <div className="container mx-auto p-4">
      <form onSubmit={handleSearch} className="mb-4 flex flex-col gap-4">
        <input type="text" value={searchTerm} onChange={(e) => setSearchTerm(e.target.value)} placeholder="Buscar dirección..." className="border border-gray-300 p-2 rounded" />
        <button type="submit" className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
          Buscar
        </button>
        <button type="button" onClick={handleClearSearch} className="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
          Limpiar
        </button>
        <button type="button" onClick={handleIconClick} className="flex items-center justify-center p-2 rounded bg-gray-200 hover:bg-gray-300 w-fit">
          <svg xmlns="http://www.w3.org/2000/svg" width="2em" height="2em" viewBox="0 0 24 24">
            <path fill="currentColor" d="M2 6H1l4-4l4 4H8v3H6V6H4v3H2zm11 4.9l1.3 1.1H16V9h2v3h3V8h1l-5-5l-5 5h1zm.8 11.1c-.5-.9-.8-1.9-.8-3c0-1.6.6-3.1 1.7-4.1L9 10l-7 6h2v6h3v-5h4v5zm4.2-7v3h-3v2h3v3h2v-3h3v-2h-3v-3z" />
          </svg>
        </button>
        {showExtraButtons && (
          <div className="flex gap-4 mt-4">
            <button type="button" onClick={() => setShowExtraButtons(false)} className="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
              Cerrar
            </button>
            <button type="button" onClick={handlePopupToggle} className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
              Agrupar
            </button>
          </div>
        )}
      </form>
      <div className="flex flex-col gap-4">
        {Array.isArray(data) && data.length > 0 ? (
          data.map((item) => (
            <div key={item.id} className={`relative border p-4 mb-4 rounded shadow flex items-center flex-row`}>
              {item.AgrupacionParent !== '1' && (
                <div className="flex flex-row justify-center w-full">
                  {showExtraButtons && <input type="checkbox" checked={selectedItems.has(item.id)} onChange={() => handleCheckboxChange(item.id)} className="mr-4" />}
                  <div>
                    <p className="font-bold">{item.direccion}</p>
                    <p>
                      <strong>Zona:</strong> {item.zona}
                    </p>
                    <p>
                      <strong>Estado de Encargo:</strong> {item.encargoState}
                    </p>
                    <p>
                      <strong>Estado de Noticia:</strong> {item.noticiastate}
                    </p>
                  </div>
                </div>
              )}
              {item.AgrupacionParent === '1' && (
                <div className="w-full flex flex-col justify-center items-center">
                  <div className="flex flex-row justify-center items-center gap-2 w-full">
                    <div className="flex flex-row justify-center items-center gap-2">
                      <h3 className="font-bold">
                        {item.direccion} {item.Numero}
                      </h3>
                      <p>
                        <strong>Tipo:</strong> {item.TipoAgrupacion}
                      </p>
                    </div>
                    <div className="cursor-pointer" onClick={() => handleToggle(item.id)}>
                      <svg xmlns="http://www.w3.org/2000/svg" width="2em" height="2em" viewBox="0 0 24 24">
                        <path fill="currentColor" fill-rule="evenodd" d="M7 9a1 1 0 0 0-.707 1.707l5 5a1 1 0 0 0 1.414 0l5-5A1 1 0 0 0 17 9z" clip-rule="evenodd" />
                      </svg>
                    </div>
                  </div>

                  {expandedItems[item.id] && getChildDetails(item.id)}
                </div>
              )}
            </div>
          ))
        ) : (
          <p>No data available</p>
        )}
      </div>
      <div className="flex mt-4 w-full flex-row items-center justify-center">
        <div className="flex flex-row justify-center items-center gap-3">
          {/* Previous Button */}
          <button type="button" onClick={handlePrevious} disabled={currentPage === 1} className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Previous
          </button>

          {/* Page Count Display */}
          <div className="text-gray-700 font-semibold">
            Page {currentPage} of {totalPages}
          </div>

          {/* Next Button */}
          <button type="button" onClick={handleNext} disabled={currentPage === totalPages} className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
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
                    <path
                      fill="currentColor"
                      d="m7.825 13l4.9 4.9q.3.3.288.7t-.313.7q-.3.275-.7.288t-.7-.288l-6.6-6.6q-.15-.15-.213-.325T4.426 12t.063-.375t.212-.325l6.6-6.6q.275-.275.688-.275t.712.275q.3.3.3.713t-.3.712L7.825 11H19q.425 0 .713.288T20 12t-.288.713T19 13z"
                    />
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
                  <div>
                    <label className="block mb-2">Nombre:</label>
                    <input type="text" name="nombre" value={formData.nombre} onChange={handleFormChange} className="border border-gray-300 p-2 rounded w-full" />
                  </div>
                  <div>
                    <label className="block mb-2">Número:</label>
                    <input type="text" name="numero" value={formData.numero} onChange={handleFormChange} className="border border-gray-300 p-2 rounded w-full" />
                  </div>
                  <div className="flex gap-4 mt-4">
                    <button type="button" onClick={handlePopupToggle} className="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                      Cerrar
                    </button>
                    <button type="submit" className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                      Agrupar
                    </button>
                  </div>
                </form>
              </div>
            )}
            {showFormType === 'existing' && (
              <div className="relative pt-0 flex flex-col justify-center items-center">
                <div className="absolute top-0 -left-2 p-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-3 rounded" onClick={() => setShowFormType('')}>
                  <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                    <path
                      fill="currentColor"
                      d="m7.825 13l4.9 4.9q.3.3.288.7t-.313.7q-.3.275-.7.288t-.7-.288l-6.6-6.6q-.15-.15-.213-.325T4.426 12t.063-.375t.212-.325l6.6-6.6q.275-.275.688-.275t.712.275q.3.3.3.713t-.3.712L7.825 11H19q.425 0 .713.288T20 12t-.288.713T19 13z"
                    />
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
                      {parents
                        .filter((parent) => parent.TipoAgrupacion === selectedType)
                        .map((parent) => (
                          <option key={parent.id} value={parent.id}>
                            {parent.direccion} {parent.numero}
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
    </div>
  );
};

export default Table;
