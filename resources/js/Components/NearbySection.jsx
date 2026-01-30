import React, { useState, useEffect } from 'react';
import BusinessCard from './BusinessCard';
import axios from 'axios';

const NearbySection = () => {
    const [status, setStatus] = useState('initial'); // 'initial', 'requesting', 'granted', 'denied'
    const [coords, setCoords] = useState(null);
    const [radius, setRadius] = useState(25);
    const [results, setResults] = useState([]);
    const [isLoading, setIsLoading] = useState(false);
    const [showResults, setShowResults] = useState(false);

    const checkInitialState = () => {
        const locPref = localStorage.getItem('discoverkenya_location_preference');
        const visPref = localStorage.getItem('discoverkenya_visibility_preference');

        if (locPref === 'granted') {
            if (visPref === 'hidden') {
                setStatus('hidden');
            } else {
                requestLocation();
            }
        }
    };

    useEffect(() => {
        checkInitialState();
    }, []);

    const requestLocation = () => {
        setStatus('requesting');
        localStorage.setItem('discoverkenya_visibility_preference', 'shown');

        navigator.geolocation.getCurrentPosition(
            (position) => {
                localStorage.setItem('discoverkenya_location_preference', 'granted');
                setCoords({
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                });
                setStatus('granted');
                setShowResults(true);
                fetchPlaces(position.coords.latitude, position.coords.longitude, radius);
            },
            (error) => {
                localStorage.setItem('discoverkenya_location_preference', 'denied');
                setStatus('denied');
            },
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
        );
    };

    const fetchPlaces = async (lat, lng, r) => {
        setIsLoading(true);
        try {
            const response = await axios.get(route('listings.nearby'), {
                params: { latitude: lat, longitude: lng, radius: r }
            });
            setResults(response.data.businesses || []);
        } catch (error) {
            console.error("Error fetching nearby places:", error);
        } finally {
            setIsLoading(false);
        }
    };

    const handleUpdateSearch = () => {
        if (coords) {
            fetchPlaces(coords.lat, coords.lng, radius);
        }
    };

    const hideResults = () => {
        localStorage.setItem('discoverkenya_visibility_preference', 'hidden');
        setShowResults(false);
        setStatus('hidden');
    };

    return (
        <section className="places-near-me-section" style={{ padding: '60px 0', background: '#f8fafc' }}>
            <div className="container">
                <h2 style={{ fontSize: '1.8rem', fontWeight: 800, color: '#1e293b', marginBottom: '25px' }}>Places Near You</h2>

                {(status === 'initial' || status === 'denied') && (
                    <div id="locationPermissionMessage" className="location-permission-notice" style={{ background: 'white', padding: '30px', borderRadius: '12px', textAlign: 'center', boxShadow: '0 4px 6px -1px rgba(0,0,0,0.05)' }}>
                        <i className="fas fa-location-arrow" style={{ fontSize: '2rem', color: '#2563eb', marginBottom: '15px' }}></i>
                        {status === 'denied' ? (
                            <p style={{ color: '#ef4444', marginBottom: '20px' }}>Location access was denied. Please enable it in your browser settings.</p>
                        ) : (
                            <p style={{ color: '#64748b', marginBottom: '20px' }}>Enable location to find hidden gems around you.</p>
                        )}
                        <button onClick={requestLocation} className="btn btn-primary" style={{ background: '#2563eb', color: 'white', padding: '10px 25px', borderRadius: '8px' }}>

                            Enable Location
                        </button>
                    </div>
                )}

                {status === 'hidden' && (
                    <div className="text-center">
                        <button onClick={requestLocation} className="btn btn-primary" style={{ background: '#2563eb', color: 'white', padding: '10px 25px', borderRadius: '8px' }}>
                            Show Places Near Me
                        </button>
                    </div>
                )}

                {(status === 'granted' || status === 'requesting') && showResults && (
                    <>
                        <div id="nearbyControls" style={{ marginBottom: '30px' }}>
                            <div className="radius-slider-container form-group" style={{ maxWidth: '400px' }}>
                                <label htmlFor="radiusSlider" style={{ fontWeight: 600, color: '#475569' }}>
                                    Distance: <span id="radiusValue" style={{ color: '#2563eb' }}>{radius}</span> km
                                </label>
                                <input
                                    type="range"
                                    min="1"
                                    max="50"
                                    value={radius}
                                    onChange={(e) => setRadius(e.target.value)}
                                    className="price-slider w-full"
                                    id="radiusSlider"
                                />
                            </div>
                            <div style={{ display: 'flex', gap: '10px' }}>
                                <button onClick={handleUpdateSearch} className="btn btn-primary mt-2" style={{ background: '#2563eb', color: 'white', padding: '8px 20px', borderRadius: '6px' }}>
                                    Update Search
                                </button>
                                <button onClick={hideResults} className="btn btn-secondary mt-2" style={{ background: '#64748b', color: 'white', padding: '8px 20px', borderRadius: '6px' }}>
                                    Hide
                                </button>
                            </div>
                        </div>

                        <div id="nearbyResultsContainer">
                            {isLoading && (
                                <div id="nearbyLoadingSpinner" style={{ textAlign: 'center', padding: '40px' }}>
                                    <div className="spinner"></div>
                                    <p style={{ color: '#64748b', marginTop: '10px' }}>Scanning your area...</p>
                                </div>
                            )}
                            {!isLoading && results.length > 0 && (
                                <div id="nearbyPlacesResults" className="listings-grid mt-6" style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(280px, 1fr))', gap: '25px' }}>
                                    {results.map(business => (
                                        <BusinessCard key={business.id} business={business} />
                                    ))}
                                </div>
                            )}
                            {!isLoading && results.length === 0 && status === 'granted' && (
                                <p className="text-center text-gray-500">No places found in this radius. Try increasing it.</p>
                            )}
                        </div>
                    </>
                )}
            </div>
        </section>
    );
};

export default NearbySection;
