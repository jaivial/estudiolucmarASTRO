import React, { useState, useEffect, useRef } from 'react';
import axios from 'axios';
import { AiOutlineCamera, AiOutlinePlus, AiOutlineLoading, AiOutlineDelete } from 'react-icons/ai';
import Toastify from 'toastify-js';
import './ItemDetailsHeader.css';

const ItemDetailsHeader = ({ inmuebleId, onClose, address, setImages, setIsSliderLoading }) => {
  const [selectedFiles, setSelectedFiles] = useState([]);
  const [uploadStatus, setUploadStatus] = useState('');
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [uploadedImages, setUploadedImages] = useState([]);
  const [hoveredSlot, setHoveredSlot] = useState(null);
  const [selectedSlot, setSelectedSlot] = useState(null);
  const [isUploading, setIsUploading] = useState(false);
  const [isProcessing, setIsProcessing] = useState(false); // New state for file processing
  const modalRef = useRef(null);
  const containerRef = useRef(null);
  const getFileRef = useRef(null);
  const buttonUploadRef = useRef(null);
  const slotRefs = useRef([]);
  const [getImageRefreshKey, setGetImageRefreshKey] = useState(1);

  useEffect(() => {
    const loadImages = async () => {
      try {
        const response = await axios.get('http://localhost:8000/backend/itemDetails/getImages.php', {
          params: { inmueble_id: inmuebleId },
        });
        const images = response.data.images || [];
        setUploadedImages(images);
        setImages(images); // Pass the images to the parent component
      } catch (error) {
        console.error('Error fetching images:', error);
      } finally {
        setIsSliderLoading(false); // Set to false after fetching is done
      }
    };

    setIsSliderLoading(true); // Set loading true when starting
    loadImages();
    console.log('uploadedImages', uploadedImages);
  }, [inmuebleId, getImageRefreshKey]);

  const handleFileChange = async (event) => {
    const files = event.target.files;
    const processedFiles = [];

    setIsProcessing(true); // Start processing

    for (let file of files) {
      const image = await createImage(file);
      const resizedImage = await resizeImage(image, 1000); // Resize width to 1000px while maintaining aspect ratio
      const webpBlob = await convertToWebP(resizedImage);

      const webpFile = new File([webpBlob], file.name.replace(/\.[^/.]+$/, '.webp'), {
        type: 'image/webp',
      });

      // Check file size
      if (webpFile.size > 1024 * 1024) {
        // If the file is larger than 1MB
        const compressedBlob = await compressImage(webpBlob);
        processedFiles.push(new File([compressedBlob], file.name.replace(/\.[^/.]+$/, '.webp'), { type: 'image/webp' }));
      } else {
        processedFiles.push(webpFile);
      }
    }

    setSelectedFiles(processedFiles);
    setIsProcessing(false); // End processing
  };

  const createImage = (file) => {
    return new Promise((resolve) => {
      const img = new Image();
      img.src = URL.createObjectURL(file);
      img.onload = () => resolve(img);
    });
  };

  const resizeImage = (image, maxWidth) => {
    return new Promise((resolve) => {
      const canvas = document.createElement('canvas');
      const ctx = canvas.getContext('2d');
      const width = image.width;
      const height = image.height;
      const newWidth = Math.min(width, maxWidth);
      const newHeight = (newWidth / width) * height;

      canvas.width = newWidth;
      canvas.height = newHeight;
      ctx.drawImage(image, 0, 0, newWidth, newHeight);

      canvas.toBlob((blob) => resolve(blob));
    });
  };

  const convertToWebP = (blob) => {
    return new Promise((resolve) => {
      const img = new Image();
      img.src = URL.createObjectURL(blob);
      img.onload = () => {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        canvas.width = img.width;
        canvas.height = img.height;
        ctx.drawImage(img, 0, 0);

        canvas.toBlob((webpBlob) => resolve(webpBlob), 'image/webp');
      };
    });
  };

  const compressImage = (blob) => {
    return new Promise((resolve) => {
      const img = new Image();
      img.src = URL.createObjectURL(blob);
      img.onload = () => {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        canvas.width = img.width;
        canvas.height = img.height;
        ctx.drawImage(img, 0, 0);

        // Compress by reducing quality
        canvas.toBlob((compressedBlob) => resolve(compressedBlob), 'image/webp', 0.7); // Adjust quality as needed
      };
    });
  };

  const handleUpload = async () => {
    if (selectedFiles.length === 0) {
      Toastify({
        text: 'Selecciona un archivo',
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

    setIsUploading(true); // Start uploading

    try {
      const formData = new FormData();
      formData.append('inmueble_id', inmuebleId);
      formData.append('slot', selectedSlot);

      for (let file of selectedFiles) {
        formData.append('images[]', file);
      }

      const response = await axios.post('http://localhost:8000/backend/itemDetails/uploadImages.php', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });

      console.log(response.data);
      if (response.data.status === 'success') {
        setUploadStatus('Images uploaded successfully!');
        setUploadedImages((prevImages) => {
          const newImages = [...prevImages];
          // Update image display if needed
          return newImages;
        });
        Toastify({
          text: 'Fotos subidas correctamente',
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
        setGetImageRefreshKey(getImageRefreshKey + 1);
        setUploadStatus('Images uploaded successfully!');
        console.log('Refreshing getImages endpoint...');
        console.log(getImageRefreshKey);
      } else {
        setUploadStatus('Error uploading images.');
      }
    } catch (error) {
      setUploadStatus('Error uploading images.');
      console.error('Error uploading images:', error);
    } finally {
      setIsUploading(false);
      setSelectedFiles([]);
    }
  };

  const handleDeleteImage = async (index) => {
    try {
      // Prepare data for deletion
      const formData = new FormData();
      formData.append('inmueble_id', inmuebleId);
      formData.append('image_id', uploadedImages[index].id); // Use the actual image ID

      // Send delete request
      const response = await axios.post('http://localhost:8000/backend/itemDetails/deleteImage.php', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });

      if (response.data.status === 'success') {
        Toastify({
          text: 'Image deleted successfully',
          duration: 2500,
          gravity: 'top',
          position: 'center',
          style: {
            borderRadius: '10px',
            backgroundImage: 'linear-gradient(to right bottom, #00603c, #006f39, #007d31, #008b24, #069903)',
            textAlign: 'center',
          },
        }).showToast();

        // Update state to remove the deleted image
        setUploadedImages((prevImages) => {
          const newImages = [...prevImages];
          newImages.splice(index, 1); // Remove the image at the specified index
          return newImages;
        });
        setGetImageRefreshKey(getImageRefreshKey + 1);
      } else {
        Toastify({
          text: 'Error deleting image',
          duration: 2500,
          gravity: 'top',
          position: 'center',
          style: {
            borderRadius: '10px',
            backgroundImage: 'linear-gradient(to right top, #c62828, #b92125, #ac1a22, #a0131f, #930b1c)',
            textAlign: 'center',
          },
        }).showToast();
      }
    } catch (error) {
      Toastify({
        text: 'Error deleting image',
        duration: 2500,
        gravity: 'top',
        position: 'center',
        style: {
          borderRadius: '10px',
          backgroundImage: 'linear-gradient(to right top, #c62828, #b92125, #ac1a22, #a0131f, #930b1c)',
          textAlign: 'center',
        },
      }).showToast();
      console.error('Error deleting image:', error);
    }
  };

  const openModal = (slot) => {
    setSelectedSlot(slot);
    setIsModalOpen(true);
  };

  const closeModal = () => {
    setIsModalOpen(false);
    setSelectedSlot(null);
  };

  const renderSlots = () => {
    const slots = [];
    for (let i = 0; i < 12; i++) {
      const image = uploadedImages[i];
      const isEmpty = !image || !image.data;
      const isImageSlot = !isEmpty; // Whether the slot contains an image

      slots.push(
        <div
          key={i}
          className={`relative w-24 h-24 flex items-center justify-center rounded-lg ${isImageSlot ? 'border border-gray-300' : 'cursor-pointer'} ${hoveredSlot === i ? (isImageSlot ? 'bg-blue-300 opacity-100' : 'bg-gray-100 opacity-50') : ''}`}
          onMouseEnter={() => setHoveredSlot(i)} // Show hover effect
          onMouseLeave={() => setHoveredSlot(null)} // Hide hover effect
          onClick={() => (isImageSlot ? handleDeleteImage(i) : openModal(i))} // Handle click action
          ref={(el) => (slotRefs.current[i] = el)}
        >
          {isImageSlot ? <img src={`data:${image.type};base64,${image.data}`} alt={`Slot ${i}`} className="w-full h-full object-cover rounded-lg" /> : <AiOutlineCamera className={`text-gray-500 text-2xl ${hoveredSlot === i ? 'hidden' : ''}`} />}

          {isImageSlot && hoveredSlot === i && (
            <div className="absolute inset-0 flex items-center justify-center rounded-lg cursor-pointer bg-red-500 opacity-100">
              <AiOutlineDelete className="text-white text-3xl" />
            </div>
          )}
          {!isImageSlot && hoveredSlot === i && (
            <div className="absolute inset-0 flex items-center justify-center rounded-lg bg-gray-200 opacity-100 cursor-pointer">
              <AiOutlinePlus className="text-gray-500 text-2xl" />
            </div>
          )}
        </div>,
      );
    }
    return slots;
  };

  return (
    <div>
      <div className="p-4 border-b border-gray-300 flex justify-between items-center">
        <button onClick={onClose} className="text-black text-3xl">
          &larr;
        </button>
        <button onClick={() => openModal(null)} className="p-2 rounded-full border border-gray-300 hover:bg-gray-100">
          <AiOutlineCamera className="text-gray-500 text-2xl" />
        </button>
      </div>
      {isModalOpen && (
        <div className={`popupcontainer fixed inset-0 h-full overflow-y-auto bg-gray-800 bg-opacity-50 flex items-center justify-center z-[50] ${window.innerWidth < 400 ? 'pt-32 pb-20' : ''}`}>
          <div ref={modalRef} className={`bg-white p-6 rounded shadow-lg max-w-lg w-full flex flex-col items-center justify-center gap-4 ${window.innerWidth < 400 ? 'mt-32 mb-10' : ''}`}>
            <h2 className="text-xl font-semibold mb-4">Subir Imágenes</h2>

            {isUploading ? (
              <div className="flex w-full flex-row items-center justify-center h-80">
                <AiOutlineLoading className="text-blue-500 text-5xl animate-spin" />
                <span className="ml-3 text-gray-800 font-sans text-lg font-semibold">Subiendo imágenes...</span>
              </div>
            ) : (
              <div ref={containerRef} className="grid grid-cols-3 gap-6 mb-4">
                {renderSlots()}
              </div>
            )}

            {selectedSlot !== null && (
              <>
                <div className="flex mb-4 flex-col justify-center items-center w-full gap-4">
                  <input ref={getFileRef} type="file" multiple onChange={handleFileChange} className="flex-row items-center justify-center w-fit" />
                  {isProcessing && (
                    <div className="flex items-center ml-4 text-white py-2 px-4 rounded">
                      <AiOutlineLoading className="text-blue-500 text-4xl animate-spin" />
                      <span className="ml-2 text-gray-800 font-sans font-semibold">Cargando imágenes...</span>
                    </div>
                  )}
                </div>

                {!isProcessing && !isUploading && selectedFiles.length > 0 && (
                  <div className="flex flex-col items-center justify-center w-full">
                    <button
                      ref={buttonUploadRef}
                      onClick={handleUpload}
                      className={`bg-blue-500 text-white py-2 px-4 rounded ${isUploading ? 'cursor-wait' : ''}`}
                      disabled={isUploading} // Disable button while uploading
                    >
                      Subir Imágenes
                    </button>
                  </div>
                )}
              </>
            )}
            <button onClick={closeModal} className="bg-red-500 text-white py-2 px-4 rounded">
              Cerrar
            </button>
          </div>
        </div>
      )}
    </div>
  );
};

export default ItemDetailsHeader;
