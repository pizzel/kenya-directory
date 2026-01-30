import React, { useState } from 'react';
import CountyCard from './CountyCard';
import axios from 'axios';

const PopularDestinations = ({ initialCounties }) => {
    const [counties, setCounties] = useState(initialCounties || []);
    const [page, setPage] = useState(2);
    const [hasMore, setHasMore] = useState(true);
    const [isLoading, setIsLoading] = useState(false);

    const loadMore = async () => {
        setIsLoading(true);
        try {
            const response = await axios.get(route('ajax.home-section'), {
                params: { section: 'popular-counties', page: page }
            });

            // Note: Current controller returns JSON with HTML and hasMore
            if (response.data.html) {
                // We need to handle this because the controller returns HTML strings.
                // In a pure Inertia setup, the controller should return data.
                // Since I'm migrating, I'll likely update the controller later.
                // For now, I'll assume I'll update the controller to return JSON data.

                if (response.data.counties) {
                    setCounties(prev => [...prev, ...response.data.counties]);
                }
                setHasMore(response.data.hasMore);
                setPage(prev => prev + 1);
            }
        } catch (error) {
            console.error("Error loading more counties:", error);
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <section className="popular-counties content-section" style={{ padding: '60px 0', background: '#fff' }}>
            <div className="container">
                <h2 style={{ fontSize: '1.8rem', fontWeight: 800, color: '#1e293b', marginBottom: '25px' }}>Popular Destinations</h2>

                <div className="destination-grid" id="popularCountiesGrid" style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(280px, 1fr))', gap: '25px' }}>
                    {counties.map(county => (
                        <div key={county.id} className="fade-in-up">
                            <CountyCard county={county} />
                        </div>
                    ))}
                </div>

                {hasMore && (
                    <div id="loadMoreCountiesContainer" style={{ textAlign: 'center', marginTop: '40px' }}>
                        <button
                            onClick={loadMore}
                            disabled={isLoading}
                            className="btn"
                            style={{ backgroundColor: '#2563eb', color: 'white', padding: '12px 30px', borderRadius: '50px', fontWeight: 600, border: 'none', cursor: 'pointer', transition: 'background 0.2s', opacity: isLoading ? 0.7 : 1 }}
                        >
                            {isLoading ? 'Loading...' : 'Load More Destinations'}
                        </button>
                    </div>
                )}
            </div>
        </section>
    );
};

export default PopularDestinations;
