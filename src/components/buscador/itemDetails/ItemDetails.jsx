import React, { useState, useEffect, useCallback, useRef } from 'react';
import axios from 'axios';
import ItemDetailsHeader from './ItemDetailsHeader'; // Adjust the import path as needed
import { useKeenSlider } from 'keen-slider/react';
import 'keen-slider/keen-slider.min.css';
import './ItemDetailsHeader.css';

const ItemDetails = ({ id, onClose }) => {
  const [data, setData] = useState(null);
  const [images, setImages] = useState([]);
  const [loading, setLoading] = useState(true);
  const [loaded, setLoaded] = useState(false);
  const [currentSlide, setCurrentSlide] = useState(0);

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
  }, [slider, images]);

  useEffect(() => {
    axios
      .get('http://localhost:8000/backend/inmuebles/inmueblemoreinfo.php', {
        params: { id: id },
      })
      .then((response) => {
        setData(response.data);
      })
      .catch((error) => {
        console.error('Error fetching data:', error);
      });
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
    <div className="fixed inset-0 bg-white z-30">
      <ItemDetailsHeader
        onClose={onClose}
        address={data.inmueble.direccion}
        inmuebleId={data.inmueble.id}
        setImages={setImages} // Pass the callback
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
    </div>
  );
};

export default ItemDetails;
