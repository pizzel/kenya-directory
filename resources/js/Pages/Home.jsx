import React from 'react';
import { Head } from '@inertiajs/react';
import Layout from '../Layouts/Layout';
import HeroSlider from '../Components/HeroSlider';
import DiscoveryScroller from '../Components/DiscoveryScroller';
import NearbySection from '../Components/NearbySection';
import PopularDestinations from '../Components/PopularDestinations';
import ListingSection from '../Components/ListingSection';

const Home = ({ heroSliderBusinesses, discoveryCards, popularCounties, seoKeywords }) => {
    return (
        <Layout>
            <Head>
                <title>Best Things to Do in Kenya | Activities, Safaris & Experiences</title>
                <meta name="description" content="Plan your ultimate Kenya adventure. Discover top-rated activities, hidden gems, safaris, and local culture in Nairobi, Mombasa, and beyond." />
                <meta name="keywords" content={seoKeywords} />
            </Head>

            <main>
                {/* 1. HERO SLIDER SECTION */}
                {heroSliderBusinesses && heroSliderBusinesses.length > 0 ? (
                    <HeroSlider businesses={heroSliderBusinesses} />
                ) : (
                    <div className="container py-10 text-center text-gray-500">No hero items available</div>
                )}

                {/* 2. DISCOVERY COLLECTIONS */}
                {discoveryCards && <DiscoveryScroller discoveryCards={discoveryCards} />}

                {/* 3. PLACES NEAR ME */}
                <NearbySection />

                {/* 4. POPULAR COUNTIES */}
                {popularCounties && <PopularDestinations initialCounties={popularCounties} />}


                {/* 5. TRENDING */}
                <ListingSection
                    title="Trending Right Now"
                    sectionType="trending"
                    bgColor="#f8fafc"
                />

                {/* 6. NEW ARRIVALS */}
                <ListingSection
                    title="New Arrivals"
                    sectionType="new-arrivals"
                    bgColor="#fff"
                />

                {/* 7. HIDDEN GEMS */}
                <ListingSection
                    title="Discover Hidden Gems"
                    subtitle="Unearth unique spots and local favorites you won't find anywhere else."
                    sectionType="hidden-gems"
                    bgColor="#f8fafc"
                />
            </main>
        </Layout>
    );
};

export default Home;
