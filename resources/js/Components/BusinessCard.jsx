import React from 'react';

const BusinessCard = ({ business }) => {
    // --- Logic to calculate the combined Rating for the card ---
    const internalCount = business.reviews_count || (business.reviews ? business.reviews.length : 0);
    const internalAvg = business.reviews_avg_rating || (business.reviews ? business.reviews.reduce((acc, curr) => acc + curr.rating, 0) / business.reviews.length : 0);

    const googleCount = business.google_rating_count || 0;
    const googleAvg = business.google_rating || 0;

    const totalCount = internalCount + googleCount;
    let displayRating = 0;

    if (totalCount > 0) {
        displayRating = ((internalAvg * internalCount) + (googleAvg * googleCount)) / totalCount;
    }

    return (
        <div className="listing-card group" style={{ background: '#fff', borderRadius: '16px', overflow: 'hidden', boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.05)', transition: 'transform 0.2s, box-shadow 0.2s', border: '1px solid #f3f4f6' }}>
            <a href={route('listings.show', business.slug)} className="block h-full">
                <div className="relative overflow-hidden aspect-[4/3]">
                    {business.is_featured && (
                        <span style={{ position: 'absolute', top: '12px', left: '12px', background: 'rgba(255, 255, 255, 0.95)', color: '#b45309', fontSize: '0.65rem', fontWeight: '800', padding: '5px 10px', borderRadius: '20px', zIndex: 10, boxShadow: '0 4px 6px -1px rgba(0,0,0,0.1)', letterSpacing: '0.5px', border: '1px solid #fef3c7', display: 'flex', alignItems: 'center' }}>
                            <i className="fas fa-bolt" style={{ marginRight: '4px', color: '#f59e0b' }}></i> FEATURED
                        </span>
                    )}

                    <img src={business.card_image_url || `https://kenyadirectory.test/images/placeholder-card.jpg`}
                        alt={business.name}
                        loading="lazy"
                        style={{ width: '100%', height: '200px', objectFit: 'cover', transition: 'transform 0.5s' }}
                        onMouseOver={(e) => e.currentTarget.style.transform = 'scale(1.05)'}
                        onMouseOut={(e) => e.currentTarget.style.transform = 'scale(1)'}
                    />
                </div>

                <div className="p-4" style={{ padding: '15px' }}>
                    <h3 style={{ fontSize: '1.1rem', fontWeight: '700', color: '#1a202c', textDecoration: 'none', marginBottom: '5px', lineHeight: '1.4' }}>
                        {business.name.length > 35 ? business.name.substring(0, 32) + '...' : business.name}
                        {business.is_verified && (
                            <i className="fas fa-check-circle" style={{ color: '#3b82f6', fontSize: '0.8rem', marginLeft: '4px' }} title="Verified"></i>
                        )}
                    </h3>

                    <p style={{ fontSize: '0.85rem', color: '#718096', marginBottom: '12px', display: 'flex', alignItems: 'center' }}>
                        <i className="fas fa-map-marker-alt" style={{ color: '#cbd5e0', marginRight: '6px' }}></i>
                        {business.county ? business.county.name : 'Kenya'}
                    </p>

                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', paddingTop: '12px', borderTop: '1px solid #edf2f7' }}>
                        <div style={{ display: 'flex', alignItems: 'center' }}>
                            <i className="fas fa-star" style={{ color: '#fbbf24', fontSize: '0.8rem', marginRight: '4px' }}></i>
                            <span style={{ fontWeight: '700', fontSize: '0.9rem', color: '#2d3748' }}>{displayRating.toFixed(1)}</span>
                            <span style={{ color: '#a0aec0', fontSize: '0.8rem', marginLeft: '2px' }}>({totalCount})</span>
                        </div>

                        <div style={{ fontSize: '0.75rem', color: '#a0aec0' }}>
                            {new Intl.NumberFormat().format(business.views_count || 0)} views
                        </div>
                    </div>
                </div>
            </a>
        </div>
    );
};

export default BusinessCard;
