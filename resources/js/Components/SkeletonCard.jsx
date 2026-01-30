import React from 'react';

const SkeletonCard = () => {
    return (
        <div className="skeleton-card" style={{ background: '#fff', borderRadius: '12px', overflow: 'hidden', height: '100%', boxShadow: '0 2px 4px rgba(0,0,0,0.05)' }}>
            <div className="skeleton-loader" style={{ width: '100%', height: '200px' }}></div>
            <div style={{ padding: '15px' }}>
                <div className="skeleton-loader" style={{ width: '70%', height: '20px', marginBottom: '10px', borderRadius: '4px' }}></div>
                <div className="skeleton-loader" style={{ width: '40%', height: '16px', marginBottom: '15px', borderRadius: '4px' }}></div>
                <div style={{ display: 'flex', gap: '10px' }}>
                    <div className="skeleton-loader" style={{ width: '60px', height: '24px', borderRadius: '12px' }}></div>
                    <div className="skeleton-loader" style={{ width: '60px', height: '24px', borderRadius: '12px' }}></div>
                </div>
            </div>
        </div>
    );
};

export default SkeletonCard;
