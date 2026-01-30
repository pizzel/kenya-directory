import React, { useState } from 'react';
import { Swiper, SwiperSlide } from 'swiper/react';
import { Autoplay, Pagination, Navigation, EffectFade } from 'swiper/modules';
import { usePage } from '@inertiajs/react';

// Import Swiper styles
import 'swiper/css';
import 'swiper/css/pagination';
import 'swiper/css/navigation';
import 'swiper/css/effect-fade';

const HeroSlider = ({ businesses }) => {
    const { auth } = usePage().props;

    const [wishlistStates, setWishlistStates] = useState(
        businesses.reduce((acc, b) => {
            acc[b.id] = b.is_wishlisted || false;
            return acc;
        }, {})
    );

    const [processing, setProcessing] = useState({});

    const toggleWishlist = async (business) => {
        if (!auth.user) {
            window.location.href = route('login');
            return;
        }

        setProcessing(prev => ({ ...prev, [business.id]: true }));

        try {
            const response = await fetch(route('wishlist.business.toggle', business.slug), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ action: 'add' })
            });

            const data = await response.json();
            if (data.success) {
                setWishlistStates(prev => ({ ...prev, [business.id]: data.is_in_wishlist }));
            }
        } catch (error) {
            console.error('Error toggling wishlist:', error);
        } finally {
            setProcessing(prev => ({ ...prev, [business.id]: false }));
        }
    };

    if (!businesses || businesses.length === 0) return null;

    return (
        <section className="hero-slider-section">
            <Swiper
                modules={[Autoplay, Pagination, Navigation, EffectFade]}
                effect="fade"
                fadeEffect={{ crossFade: true }}
                autoplay={{ delay: 7000, disableOnInteraction: false }}
                pagination={{ clickable: true }}
                navigation={true}
                loop={false}
                style={{ height: '100%' }}
                className="heroSwiper"
            >
                {businesses.map((business, index) => (
                    <SwiperSlide key={business.id} className={index === 0 ? 'hero-slide-first' : ''}>
                        <picture className="hero-slide-picture" style={{ width: '100%', height: '100%', display: 'block' }}>
                            <source
                                media="(max-width: 767px)"
                                srcSet={business.hero_image_url_mobile}
                                sizes="100vw"
                            />
                            <source
                                media="(min-width: 768px)"
                                srcSet={business.hero_image_url}
                                sizes="100vw"
                            />
                            <img
                                src={business.hero_image_url}
                                alt={business.name}
                                className="hero-slide-image"
                                fetchPriority={index === 0 ? "high" : "low"}
                            />
                        </picture>

                        <div className="slide-content container" style={{ position: 'absolute', bottom: '60px', left: '50%', transform: 'translateX(-50%)', textAlign: 'center', color: 'white', width: '100%', maxWidth: '900px', padding: '15px', zIndex: 2 }}>
                            <h1 className={index === 0 ? 'fade-in-up' : 'fade-in-up'} // Note: Blade removed it for index 0, but React animations might be different
                                style={{ fontSize: 'clamp(1.75rem, 5vw, 3rem)', fontWeight: 800, marginBottom: '10px', textShadow: '0 4px 10px rgba(0,0,0,0.5)' }}>
                                {business.name.length > 50 ? business.name.substring(0, 47) + '...' : business.name}
                            </h1>

                            {business.county && (
                                <p className="fade-in-up" style={{ fontSize: '1.2rem', fontWeight: 500, marginBottom: '20px', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '8px' }}>
                                    <i className="fas fa-map-marker-alt" style={{ color: '#f59e0b' }}></i> {business.county.name}
                                </p>
                            )}

                            <div className="hero-actions fade-in-up" style={{ display: 'flex', gap: '15px', justifyContent: 'center', alignItems: 'center', flexWrap: 'wrap' }}>
                                <a href={route('listings.show', business.slug)} className="btn btn-primary slide-cta-button" style={{ padding: '12px 30px', fontSize: '1.1rem', fontWeight: 600, borderRadius: '50px', background: '#2563eb', border: 'none', boxShadow: '0 4px 15px rgba(37, 99, 235, 0.4)', color: 'white', textDecoration: 'none' }}>
                                    Discover More
                                </a>

                                <button
                                    type="button"
                                    className="btn hero-wishlist-btn"
                                    onClick={() => toggleWishlist(business)}
                                    disabled={processing[business.id]}
                                    style={{ padding: '12px 25px', fontSize: '1.1rem', fontWeight: 600, borderRadius: '50px', background: 'rgba(255, 255, 255, 0.2)', border: '1px solid rgba(255, 255, 255, 0.6)', color: 'white', backdropFilter: 'blur(5px)', display: 'flex', alignItems: 'center', gap: '8px' }}
                                >
                                    <i className={processing[business.id] ? 'fas fa-spinner fa-spin' : (wishlistStates[business.id] ? 'fas fa-heart' : 'far fa-heart')} style={{ color: wishlistStates[business.id] ? '#ef4444' : 'white' }}></i>
                                    <span className="btn-text">{wishlistStates[business.id] ? 'Saved' : 'Add to Bucket List'}</span>
                                </button>
                            </div>
                        </div>
                    </SwiperSlide>
                ))}
            </Swiper>
        </section>
    );
};

export default HeroSlider;
