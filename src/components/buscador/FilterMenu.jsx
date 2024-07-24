import React, { useState, useEffect, useCallback } from 'react';
import Select from 'react-select';
import axios from 'axios';

const FilterMenu = ({ searchTerm, currentPage, data, setData, setCurrentPage, setTotalPages, setLoading }) => {
  const [alphabeticalOrder, setAlphabeticalOrder] = useState('none');
  const [selectedZone, setSelectedZone] = useState(null);
  const [selectedResponsable, setSelectedResponsable] = useState(null);
  const [filterNoticia, setFilterNoticia] = useState(null);
  const [filterEncargo, setFilterEncargo] = useState(null);
  const [superficieRange, setSuperficieRange] = useState([0, 1000]);
  const [yearRange, setYearRange] = useState([1900, new Date().getFullYear()]);
  const [zones, setZones] = useState([]);
  const [responsables, setResponsables] = useState([]);
  const [filters, setFilters] = useState(null);

  const alphabeticalOrderOptions = [
    { value: 'asc', label: 'Alphabetical Asc' },
    { value: 'desc', label: 'Alphabetical Desc' },
  ];

  const filterOptions = [
    { value: 'any', label: 'Any' },
    { value: '1', label: 'Yes' },
    { value: '0', label: 'No' },
  ];

  // Handler for Filter Noticia Change
  const handleFilterNoticiaChange = (selectedOption) => {
    setFilterNoticia(selectedOption ? selectedOption.value : null);
  };

  // Handler for Filter Encargo Change
  const handleFilterEncargoChange = (selectedOption) => {
    setFilterEncargo(selectedOption ? selectedOption.value : null);
  };

  const zoneOptions = zones.map((zone) => ({ value: zone, label: zone }));
  const responsableOptions = responsables.map((responsable) => ({ value: responsable, label: responsable }));

  // Fetch zones and responsables
  const fetchOptions = useCallback(async () => {
    try {
      const [zonesResponse, responsablesResponse] = await Promise.all([axios.get('http://localhost:8000/backend/zonas/getZonesSelect.php'), axios.get('http://localhost:8000/backend/users/getResponsablesSelect.php')]);

      setZones(zonesResponse.data);
      setResponsables(responsablesResponse.data);
    } catch (error) {
      console.error('Error fetching options:', error);
    }
  }, []);

  useEffect(() => {
    fetchOptions();
  }, [fetchOptions]);

  // Notify parent component when filters change
  useEffect(() => {
    setFilters({
      alphabeticalOrder: alphabeticalOrder,
      selectedZone: selectedZone?.value || '',
      selectedResponsable: selectedResponsable?.value || '',
      filterNoticia: filterNoticia !== null ? filterNoticia : '',
      filterEncargo: filterEncargo !== null ? filterEncargo : '',
      superficieMin: superficieRange[0],
      superficieMax: superficieRange[1],
      yearMin: yearRange[0],
      yearMax: yearRange[1],
      itemsPerPage: 10, // Adjust as needed
      page: currentPage,
    });
    console.log('filters', filters);
  }, [alphabeticalOrder, selectdZone, selectedResponsable, filterNoticia, filterEncargo, superficieRange, yearRange]);

  const fetchData = async (page, term) => {
    setLoading(true);
    try {
      const response = await axios.get('http://localhost:8000/backend/inmuebles/tabla.php', {
        params: {
          ...filters,
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
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchData(currentPage, searchTerm);
  }, [searchTerm]);

  return (
    <div className="flex flex-col gap-4 p-4">
      {/* Alphabetical Order Select */}
      <Select options={alphabeticalOrderOptions} onChange={(option) => setAlphabeticalOrder(option?.value || 'none')} value={alphabeticalOrderOptions.find((option) => option.value === alphabeticalOrder)} className="w-full" placeholder="Order Alphabetically" />

      {/* Zone Select */}
      <Select options={zoneOptions} onChange={setSelectedZone} value={selectedZone} placeholder="Select Zone" className="w-full" />

      {/* Responsable Select */}
      <Select options={responsableOptions} onChange={setSelectedResponsable} value={selectedResponsable} placeholder="Select Responsable" className="w-full" />

      {/* Filter Noticia Select */}
      <Select options={filterOptions} onChange={handleFilterNoticiaChange} value={filterOptions.find((option) => option.value === filterNoticia)} placeholder="Select Filter" className="w-full" />

      {/* Filter Encargo Select */}
      <Select options={filterOptions} onChange={handleFilterEncargoChange} value={filterOptions.find((option) => option.value === filterEncargo)} placeholder="Select Filter" className="w-full" />

      {/* Superficie Range */}
      <div className="flex gap-4">
        <input type="number" value={superficieRange[0]} onChange={(e) => setSuperficieRange([+e.target.value, superficieRange[1]])} placeholder="Min" className="border rounded p-2 w-full" />
        <input type="number" value={superficieRange[1]} onChange={(e) => setSuperficieRange([superficieRange[0], +e.target.value])} placeholder="Max" className="border rounded p-2 w-full" />
      </div>

      {/* Year Range */}
      <div className="flex gap-4">
        <input type="number" value={yearRange[0]} onChange={(e) => setYearRange([+e.target.value, yearRange[1]])} placeholder="Start Year" className="border rounded p-2 w-full" />
        <input type="number" value={yearRange[1]} onChange={(e) => setYearRange([yearRange[0], +e.target.value])} placeholder="End Year" className="border rounded p-2 w-full" />
      </div>
    </div>
  );
};

export default FilterMenu;
