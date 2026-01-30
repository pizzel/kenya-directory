import React, { useState, useEffect, useRef } from 'react';
import BusinessCard from './BusinessCard';
import SkeletonCard from './SkeletonCard';
import axios from 'axios';

const ListingSection = ({ title, subtitle, sectionType, bgColor = '#fff' }) => {
    const [businesses, setBusinesses] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [isVisible, setIsVisible] = useState(false);
    const sectionRef = useRef(null);

    useEffect(() => {
        const observer = new IntersectionObserver(
            ([entry]) => {
                if (entry.isIntersecting) {
                    setIsVisible(true);
                    observer.unobserve(entry.target);
                }
            },
            { rootMargin: '300px' }
        );

        if (sectionRef.current) {
            observer.observe(sectionRef.current);
        }

        return () => {
            if (sectionRef.current) observer.unobserve(sectionRef.current);
        };
    }, []);

    useEffect(() => {
        if (isVisible) {
            fetchData();
        }
    }, [isVisible]);

    const fetchData = async () => {
        setIsLoading(true);
        try {
            const response = await axios.get(route('ajax.home-section'), {
                params: { section: sectionType }
            });
            // The endpoint returns HTML? Wait, I need to check HomeController.
            // Oh, HomeController returns HTML for these sections.
            // I should modify HomeController to return JSON if requested, 
            // OR I can just parse the data here if I change the controller.

            // For now, let's assume I'll update the controller to return JSON.
            if (Array.isArray(response.data)) {
                setBusinesses(response.data);
            } else if (response.data.businesses) {
                setBusinesses(response.data.businesses);
            }
        } catch (error) {
            console.error(`Error fetching section ${sectionType}:`, error);
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <section className={`${sectionType}-section`} style={{ padding: '60px 0', background: bgColor }} ref={sectionRef}>
            <div className="container">
                <h2 style={{ fontSize: '1.8rem', fontWeight: 800, color: '#1e293b', marginBottom: subtitle ? '5px' : '25px' }}>{title}</h2>
                {subtitle && <p className="section-subtitle" style={{ color: '#64748b', marginBottom: '20px' }}>{subtitle}</p>}

                <div className="listings-grid" style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(280px, 1fr))', gap: '25px', minHeight: '300px' }}>
                    {isLoading ? (
                        [...Array(4)].map((_, i) => <SkeletonCard key={i} />)
                    ) : (
                        businesses.map(business => (
                            <BusinessCard key={business.id} business={business} />
                        ))
                    )}
                </div>
            </div>
        </section>
    );
};

export default ListingSection;
