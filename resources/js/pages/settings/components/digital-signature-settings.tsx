import { useState, useRef, useEffect } from 'react';
import { useTranslation } from 'react-i18next';
import { router, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { PenTool, Upload, Trash2, Save, Eraser, CheckCircle2 } from 'lucide-react';

interface DigitalSignatureSettingsProps {
  userSettings?: Record<string, string>;
  auth?: any;
}

export default function DigitalSignatureSettings({ userSettings = {}, auth }: DigitalSignatureSettingsProps) {
  const { t } = useTranslation();
  const { baseUrl, companyAllSetting = {} } = usePage().props as any;
  const mergedSettings = { ...companyAllSetting, ...userSettings };

  const [enabled, setEnabled] = useState(mergedSettings.digital_signature_enabled === 'on');
  const [autoSign, setAutoSign] = useState(mergedSettings.digital_signature_auto_sign !== 'off');
  const [signerName, setSignerName] = useState(mergedSettings.digital_signature_name || '');
  const [signerTitle, setSignerTitle] = useState(mergedSettings.digital_signature_title || 'Authorized Signatory');
  const [stampText, setStampText] = useState(mergedSettings.digital_signature_stamp_text || 'DIGITALLY SIGNED & VERIFIED');
  const [selectedFile, setSelectedFile] = useState<File | null>(null);
  const [selectedStampFile, setSelectedStampFile] = useState<File | null>(null);
  const [hasDrawn, setHasDrawn] = useState(false);
  const [isDrawing, setIsDrawing] = useState(false);
  const [isSaving, setIsSaving] = useState(false);

  const canvasRef = useRef<HTMLCanvasElement>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);
  const stampInputRef = useRef<HTMLInputElement>(null);

  const currentPath = mergedSettings.digital_signature_path || '';
  const currentStampPath = mergedSettings.digital_stamp_path || '';
  const originBase = (baseUrl || (typeof window !== 'undefined' ? window.location.origin : '')).replace(/\/$/, '');
  const storedSignatureUrl = currentPath ? `${originBase}/storage/${currentPath.replace(/^\/+/, '')}` : '';
  const storedStampUrl = currentStampPath ? `${originBase}/storage/${currentStampPath.replace(/^\/+/, '')}` : '';

  const userRoles = auth?.user?.roles || [];
  const userPermissions = auth?.user?.permissions || [];
  const canEdit =
    userRoles.includes('company') ||
    userRoles.includes('superadmin') ||
    userPermissions.includes('manage-company-settings') ||
    userPermissions.includes('manage-settings');

  useEffect(() => {
    const canvas = canvasRef.current;
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (ctx) {
      ctx.lineWidth = 2.5;
      ctx.lineCap = 'round';
      ctx.lineJoin = 'round';
      ctx.strokeStyle = '#0f172a';
    }
  }, []);

  const getCoords = (e: React.MouseEvent<HTMLCanvasElement> | React.TouchEvent<HTMLCanvasElement>) => {
    const canvas = canvasRef.current;
    if (!canvas) return { x: 0, y: 0 };
    const rect = canvas.getBoundingClientRect();
    const clientX = 'touches' in e ? e.touches[0].clientX : e.clientX;
    const clientY = 'touches' in e ? e.touches[0].clientY : e.clientY;
    return {
      x: ((clientX - rect.left) * canvas.width) / rect.width,
      y: ((clientY - rect.top) * canvas.height) / rect.height,
    };
  };

  const startDraw = (e: React.MouseEvent<HTMLCanvasElement> | React.TouchEvent<HTMLCanvasElement>) => {
    if (!canEdit) return;
    const canvas = canvasRef.current;
    const ctx = canvas?.getContext('2d');
    if (!canvas || !ctx) return;
    const { x, y } = getCoords(e);
    ctx.beginPath();
    ctx.moveTo(x, y);
    setIsDrawing(true);
    setHasDrawn(true);
  };

  const draw = (e: React.MouseEvent<HTMLCanvasElement> | React.TouchEvent<HTMLCanvasElement>) => {
    if (!isDrawing || !canEdit) return;
    const canvas = canvasRef.current;
    const ctx = canvas?.getContext('2d');
    if (!canvas || !ctx) return;
    const { x, y } = getCoords(e);
    ctx.lineTo(x, y);
    ctx.stroke();
  };

  const stopDraw = () => {
    setIsDrawing(false);
  };

  const clearCanvas = () => {
    const canvas = canvasRef.current;
    const ctx = canvas?.getContext('2d');
    if (!canvas || !ctx) return;
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    setHasDrawn(false);
  };

  const handleSave = () => {
    setIsSaving(true);
    const formData = new FormData();
    formData.append('digital_signature_enabled', enabled ? 'on' : 'off');
    formData.append('digital_signature_auto_sign', autoSign ? 'on' : 'off');
    formData.append('digital_signature_name', signerName);
    formData.append('digital_signature_title', signerTitle);
    formData.append('digital_signature_stamp_text', stampText);

    if (selectedFile) {
      formData.append('signature_file', selectedFile);
    } else if (hasDrawn && canvasRef.current) {
      formData.append('signature_data_url', canvasRef.current.toDataURL('image/png'));
    }

    if (selectedStampFile) {
      formData.append('stamp_file', selectedStampFile);
    }

    router.post(route('settings.digital-signature.update'), formData, {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => {
        setSelectedFile(null);
        setSelectedStampFile(null);
        clearCanvas();
        if (fileInputRef.current) fileInputRef.current.value = '';
        if (stampInputRef.current) stampInputRef.current.value = '';
      },
      onFinish: () => setIsSaving(false),
    });
  };

  const handleDeleteSignature = () => {
    setIsSaving(true);
    router.delete(route('settings.digital-signature.destroy'), {
      preserveScroll: true,
      onFinish: () => setIsSaving(false),
    });
  };

  return (
    <Card>
      <CardHeader className="flex flex-row items-center justify-between">
        <div>
          <CardTitle className="flex items-center gap-2 text-lg">
            <PenTool className="h-5 w-5" />
            {t('Digital Signature & Stamp')}
          </CardTitle>
          <CardDescription>
            {t('Configure your official company signature and stamp for printed and downloaded documents.')}
          </CardDescription>
        </div>
        {canEdit && (
          <Button onClick={handleSave} disabled={isSaving} size="sm" className="gap-1.5">
            <Save className="h-4 w-4" />
            {isSaving ? t('Saving...') : t('Save Signature Settings')}
          </Button>
        )}
      </CardHeader>

      <CardContent className="space-y-6">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div className="flex items-center justify-between rounded-lg border p-4">
            <div className="space-y-0.5">
              <Label htmlFor="sig-enabled" className="font-medium">{t('Enable Digital Signature')}</Label>
              <p className="text-xs text-muted-foreground">
                {t('Allow documents (Invoices, POs, Proposals, C&F) to carry your digital signature.')}
              </p>
            </div>
            <Switch
              id="sig-enabled"
              checked={enabled}
              onCheckedChange={setEnabled}
              disabled={!canEdit}
            />
          </div>

          <div className="flex items-center justify-between rounded-lg border p-4">
            <div className="space-y-0.5">
              <Label htmlFor="sig-autosign" className="font-medium">{t('Auto-Sign on Print & Download')}</Label>
              <p className="text-xs text-muted-foreground">
                {t('When ON, all printed/PDF documents are signed automatically. When OFF, you can sign manually before printing.')}
              </p>
            </div>
            <Switch
              id="sig-autosign"
              checked={autoSign}
              onCheckedChange={setAutoSign}
              disabled={!canEdit || !enabled}
            />
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div className="space-y-2">
            <Label htmlFor="signer_name">{t('Signatory Name')}</Label>
            <Input
              id="signer_name"
              value={signerName}
              onChange={(e) => setSignerName(e.target.value)}
              placeholder={t('e.g. J. Mbwambo')}
              disabled={!canEdit}
            />
          </div>
          <div className="space-y-2">
            <Label htmlFor="signer_title">{t('Signatory Title / Role')}</Label>
            <Input
              id="signer_title"
              value={signerTitle}
              onChange={(e) => setSignerTitle(e.target.value)}
              placeholder={t('e.g. Managing Director')}
              disabled={!canEdit}
            />
          </div>
          <div className="space-y-2">
            <Label htmlFor="stamp_text">{t('Digital Seal / Stamp Label')}</Label>
            <Input
              id="stamp_text"
              value={stampText}
              onChange={(e) => setStampText(e.target.value)}
              placeholder={t('DIGITALLY SIGNED & VERIFIED')}
              disabled={!canEdit}
            />
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div className="space-y-2">
            <div className="flex items-center justify-between">
              <Label>{t('Draw Signature Pad')}</Label>
              <Button type="button" variant="ghost" size="sm" onClick={clearCanvas} disabled={!canEdit} className="h-7 gap-1 text-xs">
                <Eraser className="h-3.5 w-3.5" />
                {t('Clear')}
              </Button>
            </div>
            <canvas
              ref={canvasRef}
              width={480}
              height={150}
              onMouseDown={startDraw}
              onMouseMove={draw}
              onMouseUp={stopDraw}
              onMouseLeave={stopDraw}
              onTouchStart={startDraw}
              onTouchMove={draw}
              onTouchEnd={stopDraw}
              className="w-full h-36 rounded-md border border-dashed bg-white cursor-crosshair touch-none"
            />
            <p className="text-xs text-muted-foreground">
              {t('Draw your signature above, or upload a transparent PNG/SVG signature image on the right.')}
            </p>
          </div>

          <div className="space-y-3">
            <Label htmlFor="sig-file-upload">{t('Or Upload Signature Image (.png, .jpg, .svg)')}</Label>
            <Input
              id="sig-file-upload"
              ref={fileInputRef}
              type="file"
              accept=".png,.jpg,.jpeg,.svg,.webp,image/*"
              disabled={!canEdit}
              onChange={(e) => setSelectedFile(e.target.files?.[0] || null)}
            />
            {selectedFile && (
              <p className="text-xs text-emerald-600 flex items-center gap-1">
                <Upload className="h-3.5 w-3.5" /> {selectedFile.name}
              </p>
            )}

            <div className="rounded-md border p-3 bg-muted/20">
              <div className="flex items-center justify-between mb-2">
                <span className="text-xs font-medium text-muted-foreground">{t('Stored Signature Preview')}</span>
                {storedSignatureUrl && canEdit && (
                  <Button type="button" variant="ghost" size="sm" onClick={handleDeleteSignature} className="h-6 px-2 text-xs text-destructive hover:text-destructive">
                    <Trash2 className="h-3.5 w-3.5 mr-1" /> {t('Remove')}
                  </Button>
                )}
              </div>
              {storedSignatureUrl ? (
                <div className="flex items-center gap-3 bg-white p-2 rounded border">
                  <img src={storedSignatureUrl} alt="Stored Digital Signature" className="max-h-16 object-contain" />
                  <div className="text-xs text-emerald-700 flex items-center gap-1">
                    <CheckCircle2 className="h-4 w-4" />
                    <span>{t('Active in vault')}</span>
                  </div>
                </div>
              ) : (
                <p className="text-xs text-muted-foreground italic">{t('No signature image uploaded yet.')}</p>
              )}
            </div>

            <div className="space-y-2 pt-2 border-t">
              <Label htmlFor="stamp-file-upload">{t('Official Company Stamp Image (Optional, .png, .svg)')}</Label>
              <Input
                id="stamp-file-upload"
                ref={stampInputRef}
                type="file"
                accept=".png,.jpg,.jpeg,.svg,.webp,image/*"
                disabled={!canEdit}
                onChange={(e) => setSelectedStampFile(e.target.files?.[0] || null)}
              />
              {storedStampUrl ? (
                <div className="flex items-center gap-3 bg-white p-2 rounded border">
                  <img src={storedStampUrl} alt="Stored Company Stamp" className="max-h-12 object-contain" />
                  <span className="text-xs text-emerald-700">{t('Stamp active in storage/app/public/stamps')}</span>
                </div>
              ) : (
                <p className="text-xs text-muted-foreground">
                  {t('If no image is uploaded, an official company stamp is auto-generated from your DB company name & TIN.')}
                </p>
              )}
            </div>
          </div>
        </div>
      </CardContent>
    </Card>
  );
}

