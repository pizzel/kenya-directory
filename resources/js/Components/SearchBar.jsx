import React, { useState, useEffect, useRef } from 'react';
import { usePage } from '@inertiajs/react';
import axios from 'axios';

const SearchBar = () => {
    const { query: urlQuery, county_search_input, county_query } = usePage().props.ziggy.query || {};

    const [whatQuery, setWhatQuery] = useState(urlQuery || '');
    const [whereQuery, setWhereQuery] = useState(county_search_input || '');
    const [whereHidden, setWhereHidden] = useState(county_query || '');

    const [whatSuggestions, setWhatSuggestions] = useState([]);
    const [whereSuggestions, setWhereSuggestions] = useState([]);

    const [showWhatDropdown, setShowWhatDropdown] = useState(false);
    const [showWhereDropdown, setShowWhereDropdown] = useState(false);
    const [isLoading, setIsLoading] = useState(false);

    const searchData = useRef(null);
    const whatRef = useRef(null);
    const whereRef = useRef(null);

    const fetchSearchData = async () => {
        if (searchData.current) return;
        setIsLoading(true);
        try {
            const response = await axios.get(route('ajax.search-suggestions'));
            searchData.current = response.data;
        } catch (error) {
            console.error("Failed to load search suggestions", error);
        } finally {
            setIsLoading(false);
        }
    };

    const filterWhatSuggestions = (filter) => {
        if (!searchData.current) return;

        const filterLower = filter.toLowerCase();
        const results = [];
        const isDefaultView = filter.length === 0;

        // Activities
        if (searchData.current.activities) {
            const matches = searchData.current.activities
                .filter(a => a.n.toLowerCase().includes(filterLower))
                .slice(0, isDefaultView ? 15 : 6)
                .map(a => ({ type: 'activity', value: a.n, display: a.n, icon: a.i }));

            if (matches.length > 0) {
                results.push({ header: 'Activities', items: matches });
            }
        }

        // Collections
        if (searchData.current.collections) {
            const matches = searchData.current.collections
                .filter(c => c.t.toLowerCase().includes(filterLower))
                .slice(0, isDefaultView ? 5 : 4)
                .map(c => ({ type: 'url', value: c.t, display: c.t, icon: 'fas fa-sparkles text-yellow-500', url: '/collections/' + c.s }));

            if (matches.length > 0) {
                results.push({ header: 'Curated Guides', items: matches });
            }
        }

        // Posts
        if (searchData.current.posts) {
            const matches = searchData.current.posts
                .filter(p => p.t.toLowerCase().includes(filterLower))
                .slice(0, isDefaultView ? 5 : 4)
                .map(p => ({ type: 'url', value: p.t, display: p.t, icon: 'fas fa-newspaper text-blue-400', url: '/blog/' + p.s }));

            if (matches.length > 0) {
                results.push({ header: 'Latest Stories', items: matches });
            }
        }

        setWhatSuggestions(results);
    };

    const filterWhereSuggestions = (filter) => {
        if (!searchData.current || !searchData.current.counties) return;

        const filterLower = filter.toLowerCase();
        const matches = searchData.current.counties
            .filter(c => c.n.toLowerCase().includes(filterLower))
            .slice(0, filter.length === 0 ? 15 : 8);

        setWhereSuggestions(matches);
    };

    useEffect(() => {
        const handleClickOutside = (event) => {
            if (whatRef.current && !whatRef.current.contains(event.target)) {
                setShowWhatDropdown(false);
            }
            if (whereRef.current && !whereRef.current.contains(event.target)) {
                setShowWhereDropdown(false);
            }
        };

        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    const handleWhatFocus = async () => {
        await fetchSearchData();
        filterWhatSuggestions(whatQuery);
        setShowWhatDropdown(true);
    };

    const handleWhereFocus = async () => {
        await fetchSearchData();
        filterWhereSuggestions(whereQuery);
        setShowWhereDropdown(true);
    };

    const handleWhatChange = (e) => {
        const val = e.target.value;
        setWhatQuery(val);
        filterWhatSuggestions(val);
    };

    const handleWhereChange = (e) => {
        const val = e.target.value;
        setWhereQuery(val);
        setWhereHidden(''); // Clear hidden query when typing
        filterWhereSuggestions(val);
    };

    const selectWhat = (item) => {
        if (item.type === 'url' && item.url) {
            window.location.href = item.url;
        } else {
            setWhatQuery(item.value);
            setShowWhatDropdown(false);
        }
    };

    const selectWhere = (county) => {
        setWhereQuery(county.n);
        setWhereHidden(county.s);
        setShowWhereDropdown(false);
    };

    return (
        <section className="sticky-search-section">
            <div className="container">
                <form className="search-form" action={route('listings.search')} method="GET" autoComplete="off">
                    <div className="form-group searchable-dropdown-group what-group" style={{ flexGrow: 2 }} ref={whatRef}>
                        <label htmlFor="general-search-input">What?</label>
                        <input
                            type="text"
                            id="general-search-input"
                            name="query"
                            value={whatQuery}
                            onChange={handleWhatChange}
                            onFocus={handleWhatFocus}
                            placeholder="Ex: Safari, Java House, Camping..."
                            autoComplete="off"
                        />

                        {showWhatDropdown && (
                            <div className="dropdown-list general-dropdown-list" style={{ display: 'block' }}>
                                {isLoading && <div className="dropdown-header">Loading suggestions...</div>}
                                {!isLoading && whatSuggestions.map((group, idx) => (
                                    <React.Fragment key={idx}>
                                        <div className="dropdown-header">{group.header}</div>
                                        {group.items.map((item, iIdx) => (
                                            <div key={iIdx} className="suggestion-item" onClick={() => selectWhat(item)}>
                                                <i className={item.icon}></i> {item.display}
                                            </div>
                                        ))}
                                    </React.Fragment>
                                ))}
                            </div>
                        )}
                    </div>

                    <div className="form-group searchable-dropdown-group where-group" style={{ flexGrow: 1 }} ref={whereRef}>
                        <label htmlFor="county-search-input">Where?</label>
                        <input
                            type="text"
                            id="county-search-input"
                            name="county_search_input"
                            value={whereQuery}
                            onChange={handleWhereChange}
                            onFocus={handleWhereFocus}
                            placeholder="County or Town..."
                            autoComplete="off"
                        />
                        <input type="hidden" name="county_query" value={whereHidden} />

                        {showWhereDropdown && (
                            <div className="dropdown-list county-dropdown-list" style={{ display: 'block' }}>
                                {isLoading && <div className="dropdown-header">Loading places...</div>}
                                {!isLoading && whereSuggestions.map((county, idx) => (
                                    <div key={idx} className="suggestion-item" onClick={() => selectWhere(county)}>
                                        <i className="fas fa-map-marker-alt"></i> {county.n}
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>

                    <button type="submit" className="search-btn">
                        <i className="fas fa-search"></i> <span className="btn-text">Search</span>
                    </button>
                </form>
            </div>
        </section>
    );
};

export default SearchBar;
