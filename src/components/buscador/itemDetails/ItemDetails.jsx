import React, { useState, useEffect, useCallback, useRef } from 'react';
import axios from 'axios';
import ItemDetailsHeader from './ItemDetailsHeader'; // Adjust the import path as needed
import { useKeenSlider } from 'keen-slider/react';
import 'keen-slider/keen-slider.min.css';
import './ItemDetailsHeader.css';
import { AiOutlineCamera, AiOutlinePlus, AiOutlineLoading } from 'react-icons/ai';
import DetailsInfoOne from './DetailsInfoOne';
import DetailsInfoTwo from './DetailsInfoTwo';

const ItemDetails = ({ id, onClose }) => {
  const [data, setData] = useState(null);
  const [images, setImages] = useState([]);
  const [loading, setLoading] = useState(true);
  const [loaded, setLoaded] = useState(false);
  const [currentSlide, setCurrentSlide] = useState(0);
  const [isSliderLoading, setIsSliderLoading] = useState(true);
  const [encargoData, setEncargoData] = useState([]);

  const [sliderRef, slider] = useKeenSlider({
    loop: true,
    slides: {
      perView: 1,
      spacing: 10,
    },
    created() {
      setLoaded(true);
    },
  });

  useEffect(() => {
    setLoading(false);
  }, []);

  useEffect(() => {
    if (!loading && slider.current) {
      slider.current.update();
    }
    console.log('slider', images);
    console.log('encargoData', encargoData);
  }, [slider, images]);

  useEffect(() => {
    axios
      .get('http://localhost:8000/backend/inmuebles/inmueblemoreinfo.php', {
        params: { id: id },
      })
      .then((response) => {
        console.log('response', response.data);
        setData(response.data);
      })
      .catch((error) => {
        console.error('Error fetching data:', error);
      });
  }, [id]);

  useEffect(() => {
    const fetchEncargoData = async () => {
      try {
        const response = await axios.get('http://localhost:8000/backend/encargos/encargosfetch.php', {
          params: {
            id: id,
          },
        });
        console.log('Encargo data:', response.data);
        setEncargoData(response.data);
      } catch (error) {
        console.error('Error fetching encargo data:', error);
      }
    };
    fetchEncargoData();
  }, [id]);

  function Arrow(props) {
    const disabled = props.disabled ? ' arrow--disabled' : '';
    return (
      <svg onClick={props.onClick} className={`arrow ${props.left ? 'arrow--left' : 'arrow--right'} ${disabled}`} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        {props.left && <path d="M16.67 0l2.83 2.829-9.339 9.175 9.339 9.167-2.83 2.829-12.17-11.996z" />}
        {!props.left && <path d="M5 3l3.057-3 11.943 12-11.943 12-3.057-3 9-9z" />}
      </svg>
    );
  }

  if (!data) {
    return <div>Loading...</div>;
  }

  return (
    <div className="fixed pb-24 inset-0 bg-white z-30 overflow-y-auto">
      {isSliderLoading && (
        <div className="bg-gray-100 w-full h-full fixed top-0 left-0 z-[100]">
          <div className="flex items-center justify-center h-full">
            <AiOutlineLoading className="text-blue-500 text-6xl animate-spin" />
            <span className="ml-4 text-gray-800 font-sans text-xl font-semibold">Cargando...</span>
          </div>
        </div>
      )}
      <ItemDetailsHeader
        onClose={onClose}
        address={data.inmueble.direccion}
        inmuebleId={data.inmueble.id}
        setImages={setImages}
        setIsSliderLoading={setIsSliderLoading} // Pass the callback
      />
      <div className="p-4 h-[300px] w-full rounded-lg">
        {/* Slider Component */}
        {images.length > 0 && (
          <div ref={sliderRef} className="keen-slider h-full">
            {images.map((image, index) => (
              <div key={index} className="keen-slider__slide h-full flex justify-center items-center">
                <img src={`data:${image.type};base64,${image.data}`} alt={`Slide ${index}`} className="w-auto h-full object-contain" />
              </div>
            ))}
            {loaded && slider.current && (
              <>
                <Arrow left onClick={(e) => e.stopPropagation() || slider.current?.prev()} disabled={currentSlide === 0} />

                <Arrow onClick={(e) => e.stopPropagation() || slider.current?.next()} disabled={currentSlide === slider.current.track.details.slides.length - 1} />
              </>
            )}
          </div>
        )}
        {images.length === 0 && <p className="text-center h-full flex flex-row justify-center items-center">No hay fotos disponibles</p>}
      </div>
      <h1 className="text-xl font-semibold text-start w-full leading-6 px-6">{data.inmueble.direccion}</h1>
      <DetailsInfoOne data={data} encargoData={encargoData} />
      <DetailsInfoTwo data={data} />
    </div>
  );
};

export default ItemDetails;
