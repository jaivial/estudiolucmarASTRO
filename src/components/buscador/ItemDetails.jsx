import React, { useEffect, useState } from 'react';
import axios from 'axios';

const ItemDetails = ({ id, onClose }) => {
  const [data, setData] = useState(null);

  useEffect(() => {
    // Fetch data when component loads
    axios
      .get(`http://localhost:8000/backend/inmuebles/inmueblemoreinfo.php`, {
        params: {
          id: id,
        },
      })
      .then((response) => {
        setData(response.data);
        console.log(response.data);
      })
      .catch((error) => {
        console.error('Error fetching data:', error);
      });
  }, [id]);

  if (!data) {
    return <div>Loading...</div>;
  }

  return (
    <div className="fixed inset-0 bg-white z-30">
      <div className="p-4">
        <button onClick={onClose} className="text-black">
          &larr; Back
        </button>
      </div>
      <div className="p-4">
        <h1>{data.inmueble.direccion}</h1>
        {/* Add more details as needed */}
      </div>
    </div>
  );
};

export default ItemDetails;
