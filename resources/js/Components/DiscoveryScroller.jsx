import React, { useRef, useEffect, useState } from 'react';
import DiscoveryCard from './DiscoveryCard';

const DiscoveryScroller = ({ discoveryCards }) => {
    const scrollerRef = useRef(null);
    const [showPrev, setShowPrev] = useState(false);
    const [showNext, setShowNext] = useState(true);

    const checkScroll = () => {
        if (scrollerRef.current) {
            const { scrollLeft, scrollWidth, clientWidth } = scrollerRef.current;
            setShowPrev(scrollLeft > 10);
            setShowNext(scrollLeft < scrollWidth - clientWidth - 10);
        }
    };

    useEffect(() => {
        checkScroll();
        window.addEventListener('resize', checkScroll);
        return () => window.removeEventListener('resize', checkScroll);
    }, []);

    const scroll = (direction) => {
        if (scrollerRef.current) {
            const amount = direction === 'left' ? -300 : 300;
            scrollerRef.current.scrollBy({ left: amount, behavior: 'smooth' });
        }
    };

    if (!discoveryCards || discoveryCards.length === 0) return null;

    return (
        <section className="discovery-collections-section" style={{ padding: '60px 0', background: '#fff' }}>
            <div className="container">
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-end', marginBottom: '25px' }}>
                    <h2 style={{ fontSize: '1.8rem', fontWeight: 800, color: '#1e293b', margin: 0 }}>Explore Collections</h2>
                    <a href={route('collections.index')} style={{ color: '#2563eb', fontWeight: 600, textDecoration: 'none', fontSize: '0.95rem' }}>
                        View All <i className="fas fa-arrow-right" style={{ fontSize: '0.8em' }}></i>
                    </a>
                </div>

                <div className="discovery-scroller-wrapper" style={{ position: 'relative' }}>
                    {showPrev && <button className="scroll-arrow prev" style={{ display: 'flex' }} onClick={() => scroll('left')}>❮</button>}
                    <div className="discovery-scroller" id="discoveryScroller" ref={scrollerRef} onScroll={checkScroll} style={{ overflowX: 'auto', scrollbarWidth: 'none', msOverflowStyle: 'none' }}>
                        <div className="discovery-collections-grid" style={{ display: 'flex', gap: '20px', paddingBottom: '10px' }}>
                            {discoveryCards.map(card => (
                                <div key={card.id} style={{ flex: '0 0 300px' }}>
                                    <DiscoveryCard collection={card} />
                                </div>
                            ))}
                        </div>
                    </div>
                    {showNext && <button className="scroll-arrow next" style={{ display: 'flex' }} onClick={() => scroll('right')}>❯</button>}
                </div>
            </div>
        </section>
    );
};

export default DiscoveryScroller;
