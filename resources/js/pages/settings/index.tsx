import { useState, Suspense, useEffect, useMemo } from 'react';
import { Head, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { ScrollArea } from '@/components/ui/scroll-area';
import { allSettingsItems } from '@/utils/settings';
import { getSettingsComponent } from '@/utils/settings-components';

export default function Settings() {
  const { t } = useTranslation();
  const { auth, globalSettings = {}, emailProviders = {}, cacheSize = '0.00' } = usePage().props as any;
  const [activeSection, setActiveSection] = useState('brand-settings');
  
  // Track visited sections so already-opened settings tabs stay in memory without remounting,
  // while initial page load only mounts the single active section for instant rendering!
  const [visitedSections, setVisitedSections] = useState<Set<string>>(() => new Set(['brand-settings']));

  const userPermissions = auth?.user?.permissions || [];
  const userRoles = auth?.user?.roles || [];
  const activatedPackages = auth?.user?.activatedPackages || [];

  const sidebarNavItems = useMemo(
    () => allSettingsItems(userPermissions, userRoles, activatedPackages, t),
    [userPermissions, userRoles, activatedPackages, t]
  );

  const visibleSections = sidebarNavItems;

  const handleNavClick = (href: string) => {
    const id = href.replace('#', '');
    setActiveSection(id);
    setVisitedSections(prev => {
      if (prev.has(id)) return prev;
      const next = new Set(prev);
      next.add(id);
      return next;
    });
    const element = document.getElementById(id);
    if (element) {
      element.scrollIntoView({ behavior: 'smooth' });
    }
  };

  useEffect(() => {
    const handleScroll = () => {
      const sections = sidebarNavItems.map(item => item.href.replace('#', ''));

      for (const sectionId of sections) {
        const element = document.getElementById(sectionId);
        if (element) {
          const rect = element.getBoundingClientRect();
          if (rect.top <= 100 && rect.bottom >= 100) {
            setActiveSection(sectionId);
            setVisitedSections(prev => {
              if (prev.has(sectionId)) return prev;
              const next = new Set(prev);
              next.add(sectionId);
              return next;
            });
            break;
          }
        }
      }
    };

    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, [sidebarNavItems]);

  return (
    <AuthenticatedLayout
      breadcrumbs={[{ label: t('Settings') }]}
      pageTitle={t('Settings')}
    >
      <Head title={t('Settings')} />

      <div className="flex flex-col md:flex-row gap-8">
        {/* Sidebar Navigation */}
        <div className="md:w-64 flex-shrink-0">
          <div className="sticky top-4">
            <ScrollArea className="h-[calc(100vh-8rem)]">
              <div className="pr-4 space-y-1">
                {visibleSections.map((item) => (
                  <Button
                    key={item.href}
                    variant="ghost"
                    className={cn('w-full justify-start', {
                      'bg-muted font-medium': activeSection === item.href.replace('#', ''),
                    })}
                    onClick={() => handleNavClick(item.href)}
                  >
                    <item.icon className="h-4 w-4 mr-2" />
                    {item.title}
                  </Button>
                ))}
              </div>
            </ScrollArea>
          </div>
        </div>

        {/* Main Content */}
        <div className="flex-1">
          <ScrollArea className="h-[calc(100vh-8rem)]">
            <div className="pr-4">
              {visibleSections.map((item) => {
                const sectionId = item.href.replace('#', '');
                const shouldMount = visitedSections.has(sectionId) || activeSection === sectionId;
                
                if (!shouldMount) {
                  return (
                    <section key={sectionId} id={sectionId} className="mb-8 min-h-[60px] border border-dashed rounded-lg p-6 flex items-center justify-between opacity-60">
                      <span className="text-sm font-medium text-muted-foreground">{item.title}</span>
                      <Button variant="outline" size="sm" onClick={() => handleNavClick(item.href)}>
                        {t('Configure')}
                      </Button>
                    </section>
                  );
                }

                const Component = getSettingsComponent(item.component, activatedPackages);
                if (!Component) return null;

                return (
                  <section key={sectionId} id={sectionId} className="mb-8">
                    <Suspense fallback={<div className="p-4 text-sm text-muted-foreground animate-pulse">{t('Loading')} {item.title}...</div>}>
                      <Component
                        userSettings={globalSettings}
                        auth={auth}
                        emailProviders={emailProviders}
                        cacheSize={cacheSize}
                      />
                    </Suspense>
                  </section>
                );
              })}
            </div>
          </ScrollArea>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}