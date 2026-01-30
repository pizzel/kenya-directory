import React from 'react';

const CountyCard = ({ county }) => {
    return (
        <a href={route('listings.county', { countySlug: county.slug })} className="county-card-link" style={{ textDecoration: 'none', color: 'inherit', display: 'block' }}>
            <div className="skeleton-county destination-card" style={{ position: 'relative', borderRadius: '12px', overflow: 'hidden', height: '250px', boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)' }}>
                <div className="card-image-container" style={{ width: '100%', height: '100%' }}>
                    <img src={county.display_image_url || '/images/placeholder-county.jpg'}
                        alt={county.name}
                        loading="lazy"
                        style={{ width: '100%', height: '100%', objectFit: 'cover', transition: 'transform 0.3s' }}
                        onMouseOver={(e) => e.currentTarget.style.transform = 'scale(1.05)'}
                        onMouseOut={(e) => e.currentTarget.style.transform = 'scale(1)'}
                    />
                </div>

                <div style={{ position: 'absolute', top: 0, left: 0, width: '100%', height: '100%', background: 'linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0) 60%)', pointerEvents: 'none' }}></div>

                <div className="destination-info" style={{ position: 'absolute', bottom: '15px', left: '15px', color: 'white', zIndex: 2, fontWeight: 700, fontSize: '1.1rem', width: '90%' }}>
                    {county.name}
                    <span style={{ fontSize: '0.85rem', opacity: 0.9, fontWeight: 400, display: 'block', marginTop: '2px' }}>
                        {county.businesses_count} Listings
                    </span>
                </div>
            </div>
        </a>
    );
};

export default CountyCard;
