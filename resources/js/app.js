
import Alpine from "alpinejs";
import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay, EffectFade } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import 'swiper/css/effect-fade';
import SimpleLightbox from "simplelightbox";
import "simplelightbox/dist/simple-lightbox.min.css";

// Initialize Alpine
window.Alpine = Alpine;
Alpine.start();

// Initialize Swiper (Modular)
Swiper.use([Navigation, Pagination, Autoplay, EffectFade]);
window.Swiper = Swiper;

// Initialize SimpleLightbox (Expose to window for inline scripts)
window.SimpleLightbox = SimpleLightbox;