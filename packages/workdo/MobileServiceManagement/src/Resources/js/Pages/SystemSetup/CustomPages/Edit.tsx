import { useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { Button } from '@/components/ui/button';
import { DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { RichTextEditor } from '@/components/ui/rich-text-editor';
import InputError from '@/components/ui/input-error';
import { SlugInputComponent } from '@/components/ui/slug-input';

interface CustomPage {
    id: number;
    name: string;
    title: string;
    slug: string;
    contents: string;
    enable_page_footer: string;
}

interface EditProps {
    customPage: CustomPage;
    onSuccess: () => void;
}

export default function Edit({ customPage, onSuccess }: EditProps) {
    const { t } = useTranslation();

    const { data, setData, put, processing, errors } = useForm({
        name: customPage.name,
        title: customPage.title,
        contents: customPage.contents,
        enable_page_footer: customPage.enable_page_footer,
        slug: customPage.slug
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('mobile-service.custom-pages.update', customPage.id), {
            onSuccess: () => {
                onSuccess();
            }
        });
    };

    return (
        <DialogContent className="max-w-4xl max-h-[90vh]">
            <DialogHeader>
                <DialogTitle>{t('Edit Custom Page')}</DialogTitle>
            </DialogHeader>

            <form onSubmit={submit} className="space-y-4">
                <div>
                    <Label htmlFor="name">{t('Name')}</Label>
                    <Input
                        id="name"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        placeholder={t('Enter page name')}
                        required
                    />
                    <InputError message={errors.name} />
                </div>

                <div>
                    <Label htmlFor="title">{t('Title')}</Label>
                    <Input
                        id="title"
                        value={data.title}
                        onChange={(e) => setData('title', e.target.value)}
                        placeholder={t('Enter page title')}
                        required
                    />
                    <InputError message={errors.title} />
                </div>
                <SlugInputComponent
                    id="slug"
                    label={t('URL Slug')}
                    value={data.slug}
                    onChange={(value) => setData('slug', value)}
                    placeholder={t('URL-friendly name (e.g., about-us, privacy-policy)')}
                    error={errors.slug}
                    disabled={!customPage.is_editable}
                    sourceValue={data.title}
                    autoGenerate={customPage.is_editable}
                    required
                />


                <div>
                    <Label htmlFor="contents">{t('Contents')}</Label>
                    <RichTextEditor
                        content={data.contents}
                        onChange={(content) => setData('contents', content)}
                        placeholder={t('Enter page contents')}
                        className="[&_.ProseMirror]:min-h-[300px]"
                        required
                    />
                    <InputError message={errors.contents} />
                </div>

                <div className="flex items-center space-x-2">
                    <Switch
                        id="enable_page_footer"
                        checked={data.enable_page_footer === 'on'}
                        onCheckedChange={(checked) => setData('enable_page_footer', checked ? 'on' : 'off')}
                    />
                    <Label htmlFor="enable_page_footer">{t('Enable Page Footer')}</Label>
                </div>

                <div className="flex justify-end gap-2">
                    <Button type="button" variant="outline" onClick={onSuccess}>
                        {t('Cancel')}
                    </Button>
                    <Button type="submit" disabled={processing}>
                        {processing ? t('Updating...') : t('Update')}
                    </Button>
                </div>
            </form>
        </DialogContent>
    );
}