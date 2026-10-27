import React, { useEffect, useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import Layout from '../../Components/Frontend/Layout';
import { ChevronRight, Circle, ChevronLeft, Calendar, MessageCircle, Star } from 'lucide-react';
import SocialLinks from '@/components/SocialLinks';
import { getImagePath } from '@/utils/helpers';

const About = () => {
    const { t } = useTranslation();
    const { mobileServiceSettings, aboutTitleSection } = usePage().props as any;
    const aboutData = aboutTitleSection || mobileServiceSettings?.about_title_section || {};
    const missions = aboutData?.missions || [];
    const teamMembers = aboutData?.team_members || [];
    const [currentMissionSlide, setCurrentMissionSlide] = useState(0);
    const [currentTeamSlide, setCurrentTeamSlide] = useState(0);
    const missionSlidesToShow = 3;
    const teamSlidesToShow = 3;
    const maxMissionSlides = Math.max(0, missions.length - missionSlidesToShow);
    const maxTeamSlides = Math.max(0, teamMembers.length - teamSlidesToShow);
    const { url } = usePage();
    const pathParts = url.split('/');
    const mobileServiceIndex = pathParts.findIndex(part => part === 'mobile-service');
    const userSlug = mobileServiceIndex !== -1 && pathParts[mobileServiceIndex - 1] ? pathParts[mobileServiceIndex - 1] : null;

    const nextMissionSlide = () => {
        setCurrentMissionSlide(prev => Math.min(prev + 1, maxMissionSlides));
    };

    const prevMissionSlide = () => {
        setCurrentMissionSlide(prev => Math.max(prev - 1, 0));
    };

    const nextTeamSlide = () => {
        setCurrentTeamSlide(prev => Math.min(prev + 1, maxTeamSlides));
    };

    const prevTeamSlide = () => {
        setCurrentTeamSlide(prev => Math.max(prev - 1, 0));
    };

    return (
        <Layout title="About Us | Mobile Service Management" activeNav="about">
            {/* Hero Section */}
            <section className="relative lg:py-16 py-10 overflow-hidden" style={{ backgroundColor: '#33312e' }}>
                <div className="absolute inset-0 opacity-10">
                    <div className="absolute w-64 h-64 rounded-full -top-20 -end-20" style={{ backgroundColor: '#c77d1f' }}></div>
                    <div className="absolute w-64 h-64 lg:w-80 lg:h-80 rounded-full bottom-0 -start-40" style={{ backgroundColor: '#c77d1f' }}></div>
                </div>

                <div className="md:container w-full mx-auto px-4 relative">
                    <div className="text-center max-w-4xl mx-auto">
                        <span className="inline-flex items-center bg-white px-4 py-1.5 rounded-full text-xs uppercase tracking-wider font-semibold md:mb-6 mb-4 shadow-sm border border-gray-200" style={{ color: '#33312e' }}>
                            <Circle className="w-3 h-3 me-2 fill-current" style={{ color: '#c77d1f' }} />
                            {t('Who We Are')}
                        </span>
                        <h2 className="text-2xl md:text-3xl lg:text-5xl font-bold mb-4 lg:mb-6 leading-tight text-white">
                            {t('About')} <span style={{ color: '#c77d1f' }}>{t('Us')}</span>
                        </h2>
                        <ul className="flex flex-wrap justify-center items-center capitalize text-white md:text-base text-sm">
                            <li className="capitalize flex items-center">
                                <Link href={route('mobile-service.frontend.index', { userSlug })}>{t('Home')}</Link>
                            </li>
                            <ChevronRight className="w-4 h-4 mx-2 flex items-center" />
                            <li className="capitalize flex items-center">{t('About Us')}</li>
                        </ul>
                    </div>
                </div>
            </section>

            {/* Our Story Section */}
            <section id="our-story" className="py-10 lg:py-16 bg-white">
                <div className="md:container mx-auto px-4 w-full">
                    {aboutData?.story_title || aboutData?.story_description ? (
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 md:gap-10 lg:gap-12 items-center">
                            <div className="order-2 lg:order-1">
                                <div className="relative">
                                    {aboutData.story_image ? (
                                        <div className="p-3 md:p-4 rounded-2xl shadow-xl" style={{ backgroundColor: '#e6ded6' }}>
                                            <img
                                                src={getImagePath(aboutData.story_image)}
                                                alt="Our team at work"
                                                className="w-full h-auto rounded-lg object-cover shadow-md"
                                            />
                                        </div>
                                    ) : (
                                        <div className="p-3 md:p-4 rounded-2xl shadow-xl" style={{ backgroundColor: '#e6ded6' }}>
                                            <img
                                                src={getImagePath('packages/workdo/MobileServiceManagement/src/Resources/assets/images/about-page-img.png')}
                                                alt="Our team at work"
                                                className="w-full h-auto rounded-lg object-cover shadow-md"
                                            />
                                        </div>
                                    )
                                    }
                                </div>
                            </div>

                            <div className="order-1 lg:order-2">
                                <span className="font-medium mb-2 block" style={{ color: '#c77d1f' }}>{t('OUR STORY')}</span>
                                <h2 className="text-2xl sm:text-3xl md:text-4xl font-bold title-font mb-4" style={{ color: '#33312e' }}>{aboutData?.story_title}</h2>

                                <div className="space-y-4 text-gray-600">
                                    <div dangerouslySetInnerHTML={{ __html: aboutData?.story_description?.replace(/&lt;br&gt;/g, '<br>').replace(/&lt;\/br&gt;/g, '') }} />
                                </div>
                            </div>
                        </div>
                    ) : (
                        <div className="text-center py-12">
                            <div className="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4" style={{ backgroundColor: '#f3f4f6' }}>
                                <MessageCircle className="w-8 h-8 text-gray-400" />
                            </div>
                            <h3 className="text-lg font-semibold text-gray-800 mb-2">{t('No Story Available')}</h3>
                            <p className="text-gray-600">{t('Our story content will appear here when added.')}</p>
                        </div>
                    )}
                </div>
            </section>

            {/* Mission & Values */}
            <section className="py-10 lg:py-16" style={{ backgroundColor: '#e6ded6' }}>
                <div className="md:container mx-auto px-4 w-full">
                    <div className="text-center mb-6 md:mb-10">
                        <span className="inline-flex items-center bg-white px-4 py-1.5 rounded-full text-xs uppercase tracking-wider font-semibold mb-4 shadow-sm border border-gray-200 mx-auto" style={{ color: '#33312e' }}>
                            <Circle className="w-3 h-3 me-2 fill-current" style={{ color: '#c77d1f' }} />
                            {t('Our Mission')}
                        </span>
                        <h2 className="text-2xl sm:text-3xl md:text-4xl font-bold title-font mb-4" style={{ color: '#33312e' }}>{aboutData?.mission_title}</h2>
                        <p className="lg:text-lg text-gray-600 max-w-3xl mx-auto" dangerouslySetInnerHTML={{ __html: aboutData?.mission_description?.replace(/&lt;br&gt;/g, '<br>').replace(/&lt;\/br&gt;/g, '') }} />
                    </div>

                    {missions.length > 0 ? (
                        <div className="relative">
                            <div className="overflow-hidden">
                                <div
                                    className="flex transition-transform duration-300 ease-in-out"
                                    style={{ transform: `translateX(-${currentMissionSlide * (100 / missionSlidesToShow)}%)` }}
                                >
                                    {missions.map((mission, index) => (
                                        <div key={index} className="w-1/3 flex-shrink-0 px-3">
                                            <div className="bg-white p-4 lg:p-6 rounded-lg h-full">
                                                <div className="w-12 h-12 lg:w-16 lg:h-16 rounded-full flex items-center justify-center mb-4" style={{ backgroundColor: '#e6ded6' }}>
                                                    <SocialLinks icon={mission.icon || 'Settings'} className="text-lg lg:text-2xl" style={{ color: '#c77d1f' }} />
                                                </div>
                                                <h3 className="text-xl font-bold mb-3" style={{ color: '#33312e' }}>{mission.title}</h3>
                                                <p className="text-gray-600 line-clamp-3" dangerouslySetInnerHTML={{ __html: mission.description?.replace(/&lt;br&gt;/g, '<br>').replace(/&lt;\/br&gt;/g, '') }} />
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>

                            {missions.length > missionSlidesToShow && (
                                <>
                                    <button
                                        onClick={prevMissionSlide}
                                        disabled={currentMissionSlide === 0}
                                        className="absolute left-4 top-1/2 -translate-y-1/2 w-9 h-9 bg-white rounded-md shadow-lg transition-colors flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed hover:text-white"
                                        style={{ border: '2px solid #c77d1f', color: '#c77d1f' }}
                                    >
                                        <ChevronLeft className="w-4 h-4" />
                                    </button>
                                    <button
                                        onClick={nextMissionSlide}
                                        disabled={currentMissionSlide >= maxMissionSlides}
                                        className="absolute right-4 top-1/2 -translate-y-1/2 w-9 h-9 bg-white rounded-md shadow-lg transition-colors flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed hover:text-white"
                                        style={{ border: '2px solid #c77d1f', color: '#c77d1f' }}
                                    >
                                        <ChevronRight className="w-4 h-4" />
                                    </button>
                                </>
                            )}
                        </div>
                    ) : (
                        <div className="text-center py-12">
                            <div className="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4" style={{ backgroundColor: '#f3f4f6' }}>
                                <Star className="w-8 h-8 text-gray-400" />
                            </div>
                            <h3 className="text-lg font-semibold text-gray-800 mb-2">{t('No Mission Available')}</h3>
                            <p className="text-gray-600">{t('Our mission and values will appear here when added.')}</p>
                        </div>
                    )}
                </div>
            </section>

            {/* Team Section */}
            <section className="py-10 lg:py-16 bg-white">
                <div className="md:container mx-auto px-4 w-full">
                    <div className="text-center mb-6 md:mb-10">
                        <span className="inline-flex items-center bg-white px-4 py-1.5 rounded-full text-xs uppercase tracking-wider font-semibold mb-4 shadow-sm border border-gray-200 mx-auto" style={{ color: '#33312e' }}>
                            <Circle className="w-3 h-3 me-2 fill-current" style={{ color: '#c77d1f' }} />
                            {t('Our Team')}
                        </span>
                        <h2 className="text-2xl sm:text-3xl md:text-4xl font-bold title-font mb-4" style={{ color: '#33312e' }}>{aboutData?.team_title}</h2>
                        <p className="lg:text-lg text-gray-600 max-w-3xl mx-auto" dangerouslySetInnerHTML={{ __html: aboutData?.team_description?.replace(/&lt;br&gt;/g, '<br>').replace(/&lt;\/br&gt;/g, '') }} />
                    </div>

                    {teamMembers.length > 0 ? (
                        <div className="relative">
                            <div className="overflow-hidden">
                                <div
                                    className="flex transition-transform duration-300 ease-in-out"
                                    style={{ transform: `translateX(-${currentTeamSlide * (100 / teamSlidesToShow)}%)` }}
                                >
                                    {teamMembers.map((member, index) => (
                                        <div key={index} className="w-1/3 flex-shrink-0 px-2">
                                            <div className="h-full team-card bg-white rounded-lg overflow-hidden border border-gray-200">
                                                <div className="relative">
                                                    <img className="w-full h-64 object-cover" src={member.image ? getImagePath(member.image) : getImagePath('packages/workdo/MobileServiceManagement/src/Resources/assets/images/default.png')} alt={member.name} />
                                                    {member.experience && (
                                                        <div className="absolute top-4 end-4 text-white text-xs font-bold px-3 py-1 rounded-full" style={{ backgroundColor: '#c77d1f' }}>
                                                            {member.experience}
                                                        </div>
                                                    )}
                                                </div>
                                                <div className="card-content p-4 transition-transform duration-300">
                                                    <h3 className="text-xl font-bold text-gray-900 mb-2">{member.name}</h3>
                                                    <p className="font-medium mb-2" style={{ color: '#c77d1f' }}>{member.position}</p>
                                                    <p className="text-gray-600 text-sm" dangerouslySetInnerHTML={{ __html: member.description?.replace(/&lt;br&gt;/g, '<br>').replace(/&lt;\/br&gt;/g, '') }} />
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>

                            {teamMembers.length > teamSlidesToShow && (
                                <>
                                    <button
                                        onClick={prevTeamSlide}
                                        disabled={currentTeamSlide === 0}
                                        className="absolute left-4 top-1/2 -translate-y-1/2 w-9 h-9 bg-white rounded-md shadow-lg transition-colors flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed hover:text-white"
                                        style={{ border: '2px solid #c77d1f', color: '#c77d1f' }}
                                    >
                                        <ChevronLeft className="w-4 h-4" />
                                    </button>
                                    <button
                                        onClick={nextTeamSlide}
                                        disabled={currentTeamSlide >= maxTeamSlides}
                                        className="absolute right-4 top-1/2 -translate-y-1/2 w-9 h-9 bg-white rounded-md shadow-lg transition-colors flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed hover:text-white"
                                        style={{ border: '2px solid #c77d1f', color: '#c77d1f' }}
                                    >
                                        <ChevronRight className="w-4 h-4" />
                                    </button>
                                </>
                            )}
                        </div>
                    ) : (
                        <div className="text-center py-12">
                            <div className="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4" style={{ backgroundColor: '#f3f4f6' }}>
                                <MessageCircle className="w-8 h-8 text-gray-400" />
                            </div>
                            <h3 className="text-lg font-semibold text-gray-800 mb-2">{t('No Team Members Available')}</h3>
                            <p className="text-gray-600">{t('Our team information will appear here when added.')}</p>
                        </div>
                    )}
                </div>
            </section>

            {/* CTA Section */}
            <section className="pb-10 lg:pb-16 bg-white text-white relative overflow-hidden">
                <div className="md:container mx-auto px-4 w-full">
                    <div className="text-white rounded-2xl p-4 md:p-8 lg:p-10 text-center relative overflow-hidden" style={{ backgroundColor: '#c77d1f' }}>
                        <div className="absolute -top-10 -end-10 w-32 h-32 rounded-full bg-white bg-opacity-10"></div>
                        <div className="absolute -bottom-10 -start-10 w-40 h-40 rounded-full bg-white bg-opacity-10"></div>

                        <h2 className="text-2xl sm:text-3xl md:text-4xl font-bold title-font mb-4">{aboutData?.about_cta_title}</h2>
                        <p className="lg:text-lg mb-6 lg:mb-8 max-w-3xl mx-auto" dangerouslySetInnerHTML={{ __html: aboutData?.about_cta_description?.replace(/&lt;br&gt;/g, '<br>').replace(/&lt;\/br&gt;/g, '') }} />
                        <div className="flex flex-wrap gap-4 justify-center">
                            <Link href={route('mobile-service.frontend.booking', { userSlug })} className="inline-flex items-center gap-2 px-5 py-3 font-medium rounded-lg transition-all hover:-translate-y-0.5 hover:bg-amber-900 hover:text-white hover:border-amber-900" style={{ backgroundColor: 'white', color: '#33312e', border: '1px solid white' }}>
                                <Calendar className="w-4 h-4" />
                                <span>{t('Book Appointment')}</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </section>
        </Layout>
    );
};

export default About;