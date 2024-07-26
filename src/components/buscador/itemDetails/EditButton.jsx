import React, { useState } from 'react';
import EditModal from './EditModal'; // Import the EditModal component
import { AiOutlineCamera, AiOutlineEdit } from 'react-icons/ai';

const EditButton = () => {
  const [isModalOpen, setModalOpen] = useState(false);

  const openModal = () => setModalOpen(true);
  const closeModal = () => setModalOpen(false);

  return (
    <div>
      <button onClick={openModal} className="p-2 rounded-full border border-gray-300 hover:bg-gray-100">
        <AiOutlineEdit className="text-gray-500 text-2xl" />
      </button>
      {isModalOpen && <EditModal closeModal={closeModal} />}
    </div>
  );
};

export default EditButton;
