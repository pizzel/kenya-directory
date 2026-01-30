import React from 'react';

const DiscoveryCard = ({ collection }) => {
    // The controller now provides card_image_url directly on the collection object
    const imageUrl = collection.card_image_url || '/images/placeholder-card.jpg';


    return (
        <a href={route('collections.show', { collection: collection.slug })} className="discovery-card group" style={{ display: 'block', position: 'relative', borderRadius: '16px', overflow: 'hidden', height: '280px', boxShadow: '0 4px 10px rgba(0,0,0,0.08)', transition: 'transform 0.3s ease' }}>
            <div className="discovery-card-image" style={{ width: '100%', height: '100%', overflow: 'hidden' }}>
                <img src={imageUrl}
                    alt={collection.title}
                    loading="lazy"
                    style={{ width: '100%', height: '100%', objectFit: 'cover', transition: 'transform 0.5s ease' }}
                    onMouseOver={(e) => e.currentTarget.style.transform = 'scale(1.05)'}
                    onMouseOut={(e) => e.currentTarget.style.transform = 'scale(1)'}
                />
            </div>

            <div style={{ position: 'absolute', top: 0, left: 0, width: '100%', height: '100%', background: 'linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.1) 60%)', pointerEvents: 'none' }}></div>

            <div style={{ position: 'absolute', bottom: '20px', left: '20px', width: 'calc(100% - 40px)', color: 'white', zIndex: 10 }}>
                <span style={{ background: 'rgba(255,255,255,0.2)', backdropFilter: 'blur(4px)', padding: '4px 10px', borderRadius: '20px', fontSize: '0.75rem', fontWeight: '700', textTransform: 'uppercase', marginBottom: '8px', display: 'inline-block' }}>
                    Collection
                </span>
                <h3 style={{ fontSize: '1.4rem', fontWeight: '800', margin: 0, color: '#fff', textShadow: '0 2px 4px rgba(0,0,0,0.3)', lineHeight: '1.2' }}>
                    {collection.title}
                </h3>
                <p style={{ marginTop: '5px', fontSize: '0.9rem', opacity: 0.9 }}>
                    {collection.businesses_count} curated places
                </p>
            </div>
        </a>
    );
};

export default DiscoveryCard;
