<?php

namespace Modules\Appointment\Database\Seeders;

use App\Models\EmailTemplate;
use App\Models\EmailTemplateLang;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class EmailTemplatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // $this->call("OthersTableSeeder");

        $emailTemplate = [
            'Appointment Status',
            'Appointment Send',
        ];

        $defaultTemplate = [
            'Appointment Status' => [
                'subject' => 'Appointment Status',
                'variables' => '{"App Name": "app_name","App Url": "app_url","Appointment Status Name": "appointment_status_name","Appointment Status": "appointment_status","Appointment Date": "appointment_date","Appointment Start Time": "appointment_start_time","Appointment End Time": "appointment_end_time", "Appointment Join Url": "appointment_join_url"}',
                'lang' => [
                    'ar' => '<p><strong>الموضوع: -إدارة الموارد البشرية / الشركة لإرسال خطاب الموافقة إليها {appointment_status} تعيين.</strong></p>
                    <p><strong>عزيزي {appointment_status_name},</strong></p>
                    <p>لقد كان طلب موعدك {appointment_status}.</p>
                    <p>عنوان URL للانضمام لموعدك هو: {appointment_join_url}.</p>
                    <p style="text-align: left;">إشعر بالحرية للوصول إلى الخارج إذا عندك أي أسئلة.</p>
                    <p style="text-align: left;">شكرا لك</p>
                    <p style="text-align: left;">Regards,</p>
                    <p style="text-align: left;">إدارة الموارد البشرية ،</p>
                    <p style="text-align: left;">{ app_name }</p>',
                    'da' => '<p><strong>Emne:-HR afdeling/Virksomhed at sende godkendelsesbrev til {appointment_status} en aftale.</strong></p>
                    <p><strong>Kære {appointment_status_name},</strong></p>
                    <p>Din aftaleanmodning har været {appointment_status}.</p>
                    <p>Din aftaletilmeldings-url er: {appointment_join_url}.</p>
                    <p>Du er velkommen til at r&aelig;kke ud, hvis du har nogen sp&oslash;rgsm&aring;l.</p>
                    <p>Tak.</p>
                    <p>Med venlig hilsen</p>
                    <p>HR-afdelingen,</p>
                    <p>{ app_name }</p>',
                    'de' => '<p><strong>Betreff: Personalabteilung/Unternehmen, an das das Genehmigungsschreiben gesendet werden soll {appointment_status} einen Termin.</strong></p>
                    <p><strong>Liebling {appointment_status_name},</strong></p>
                    <p>Ihre Terminanfrage wurde {appointment_status}.</p>
                    <p>Die Beitritts-URL für Ihren Termin lautet: {appointment_join_url}.</p>
                    <p>F&uuml;hlen Sie sich frei, wenn Sie Fragen haben.</p>
                    <p>Danke.</p>
                    <p>Betrachtet,</p>
                    <p>Personalabteilung,</p>
                    <p>{app_name}</p>',
                    'en' => '<p><strong>Subject:-HR department/Company to send approval letter to {appointment_status} a appointment.</strong></p>
                    <p><strong>Dear {appointment_status_name},</strong></p>
                    <p>Your appointment request has been {appointment_status}.</p>
                    <p>Your appointment join url is: {appointment_join_url}.</p>
                    <p>Feel free to reach out if you have any questions.</p>
                    <p>Thank you</p>
                    <p><strong>Regards,</strong></p>
                    <p><strong>HR Department,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                    'es' => '<p><strong>Asunto: -Departamento de recursos humanos/Compañía para enviar carta de aprobación a {appointment_status} una cita.</strong></p>
                    <p><strong>Estimado {appointment_status_name},</strong></p>
                    <p>Su solicitud de cita ha sido {appointment_status}.</p>
                    <p>La URL para unirse a su cita es: {appointment_join_url}.</p>
                    <p>Si&eacute;ntase libre de llegar si usted tiene alguna pregunta.</p>
                    <p>&iexcl;Gracias!</p>
                    <p>Considerando,</p>
                    <p>Departamento de Recursos Humanos,</p>
                    <p>{app_name}</p>',
                    'fr' => '<p><strong>Objet : - Le service des ressources humaines/lentreprise doit envoyer une lettre dapprobation à {appointment_status} un rendez-vous.</strong></p>
                    <p><strong>Cher {appointment_status_name},</strong></p>
                    <p>Votre demande de rendez-vous a été {appointment_status}.</p>
                    <p>L URL de votre rendez-vous est : {appointment_join_url}.</p>
                    <p>Nh&eacute;sitez pas &agrave; nous contacter si vous avez des questions.</p>
                    <p>Je vous remercie</p>
                    <p>Regards,</p>
                    <p>D&eacute;partement des RH,</p>
                    <p>{ app_name }</p>',
                    'it' => '<p><strong>Oggetto:-reparto risorse umane/azienda a cui inviare la lettera di approvazione {appointment_status} un appuntamento.</strong></p>
                    <p><strong>Caro {appointment_status_name},</strong></p>
                    <p>La tua richiesta di appuntamento è stata {appointment_status}.</p>
                    <p>L URL di iscrizione all appuntamento è: {appointment_join_url}.</p>
                    <p>Sentiti libero di raggiungere se hai domande.</p>
                    <p>Grazie</p>
                    <p>Riguardo,</p>
                    <p>Dipartimento HR,</p>
                    <p>{app_name}</p>',
                    'ja' => '<p><strong>件名:-承認レターの送信先となる人事部門/会社 {appointment_status} 約束。</strong></p>
                    <p><strong>親愛なる {appointment_status_name},</strong></p>
                    <p>あなたの予約リクエストは {appointment_status}.</p>
                    <p>予約の参加 URL は次のとおりです。 {appointment_join_url}.</p>
                    <p>質問がある場合は、自由に連絡してください。</p>
                    <p>ありがとう</p>
                    <p>よろしく</p>
                    <p>HR 部門</p>
                    <p>{app_name}</p>',
                    'nl' => '<p><strong>Onderwerp: - HR-afdeling/bedrijf om goedkeuringsbrief naar te sturen {appointment_status} een afspraak.</strong></p>
                    <p><strong>Beste {appointment_status_name},</strong></p>
                    <p>Uw afspraakverzoek is geweest {appointment_status}.</p>
                    <p>De deelname-URL voor uw afspraak is: {appointment_join_url}.</p>
                    <p>Voel je vrij om uit te reiken als je vragen hebt.</p>
                    <p>Dank u wel</p>
                    <p>Betreft:</p>
                    <p>HR-afdeling,</p>
                    <p>{ app_name }</p>',
                    'pl' => '<p><strong>Temat: Dział HR/Firma, do której ma zostać wysłany list zatwierdzający {appointment_status} spotkanie.</strong></p>
                    <p><strong>Droga {appointment_status_name},</strong></p>
                    <p>Twoja prośba o spotkanie została {appointment_status}.</p>
                    <p>Adres URL dołączenia do Twojego spotkania to: {appointment_join_url}.</p>
                    <p>Czuj się swobodnie, jeśli masz jakieś pytania.</p>
                    <p>Dziękujemy</p>
                    <p>W odniesieniu do</p>
                    <p>Dział HR,</p>
                    <p>{app_name }</p>',
                    'ru' => '<p><strong>Тема:-Отдел кадров/компания, которому необходимо отправить письмо об утверждении {appointment_status} назначение.</strong></p>
                    <p><strong>Дорогой {appointment_status_name},</strong></p>
                    <p>Ваш запрос на встречу был {appointment_status}.</p>
                    <p>URL-адрес вашей записи на прием: {appointment_join_url}.</p>
                    <p>Не стеснитесь, если у вас есть вопросы.</p>
                    <p>Спасибо.</p>
                    <p>С уважением,</p>
                    <p>Отдел кадров,</p>
                    <p>{ app_name }</p>',
                    'pt' => '<p><strong>Assunto:-Departamento de RH/Empresa para enviar carta de aprovação {appointment_status} um compromisso.</strong></p>
                    <p><strong>Querido {appointment_status_name},</strong></p>
                    <p>Sua solicitação de agendamento foi {appointment_status}.</p>
                    <p>O URL de inscrição do seu compromisso é: {appointment_join_url}.</p>
                    <p style="font-size: 14.4px;">Sinta-se &agrave; vontade para alcan&ccedil;ar fora se voc&ecirc; tiver alguma d&uacute;vida.</p>
                    <p style="font-size: 14.4px;">Obrigado</p>
                    <p style="font-size: 14.4px;">Considera,</p>
                    <p style="font-size: 14.4px;">Departamento de RH,</p>
                    <p style="font-size: 14.4px;">{app_name}</p>',
                ],
            ],
            'Appointment Send' => [
                'subject' => 'Appointment Send',
                'variables' => '{"App Name": "app_name","App Url": "app_url", "Appointment User Name": "appointment_user_name", "Appointment User Email": "appointment_user_email", "Appointment Unique Id": "appointment_unique_id"}',
                'lang' => [
                    'ar' => '<p><strong>عزيزي {appointment_user_name},</strong></p>
                    <p>معرف الموعد الخاص بك هو <br> معرف الموعد: {appointment_unique_id} <br> والبريد الإلكتروني هو <br> البريد الإلكتروني للموعد: {appointment_user_email}.</p>
                    <p>لا تتردد في التواصل معنا إذا كان لديك أي أسئلة.</p>
                    <p>شكرًا لك</p>
                    <p><strong>يعتبر،</strong></p>
                    <p><strong>قسم الموارد البشرية،</strong></p>
                    <p><strong>{app_name}</strong></p>',

                    'da' => '<p><strong>Kære {appointment_user_name},</strong></p>
                    <p>Dit aftale-id er <br> Aftale-id: {appointment_unique_id} <br> og e-mail er <br> E-mail til aftale: {appointment_user_email}.</p>
                    <p>Du er velkommen til at kontakte os, hvis du har spørgsmål.</p>
                    <p>tak skal du have</p>
                    <p><strong>Med venlig hilsen</strong></p>
                    <p><strong>HR afdeling,</strong></p>
                    <p><strong>{app_name}</strong></p>',

                    'de' => '<p><strong>Liebling {appointment_user_name},</strong></p>
                    <p>Ihre Termin-ID lautet <br> Termin-ID: {appointment_unique_id} <br> und E-Mail ist <br> Termin-E-Mail: {appointment_user_email}.</p>
                    <p>Wenn Sie Fragen haben, können Sie sich jederzeit an uns wenden.</p>
                    <p>Danke</p>
                    <p><strong>Grüße,</strong></p>
                    <p><strong>Personalabteilung,</strong></p>
                    <p><strong>{app_name}</strong></p>',

                    'en' => '<p><strong>Dear {appointment_user_name},</strong></p>
                    <p>Your appointment id is <br> Appointment Id: {appointment_unique_id} <br> and email is <br> Appointment Email: {appointment_user_email}.</p>
                    <p>Feel free to reach out if you have any questions.</p>
                    <p>Thank you</p>
                    <p><strong>Regards,</strong></p>
                    <p><strong>HR Department,</strong></p>
                    <p><strong>{app_name}</strong></p>',

                    'es' => '<p><strong>Estimado {appointment_user_name},</strong></p>
                    <p>Su identificación de cita es <br> Identificación de la cita: {appointment_unique_id} <br> y el correo electrónico es <br> Correo electrónico de cita: {appointment_user_email}.</p>
                    <p>No dude en comunicarse si tiene alguna pregunta.</p>
                    <p>Gracias</p>
                    <p><strong>Saludos,</strong></p>
                    <p><strong>Departamento de Recursos Humanos,</strong></p>
                    <p><strong>{app_name}</strong></p>',

                    'fr' => '<p><strong>Cher {appointment_user_name},</strong></p>
                    <p>Votre identifiant de rendez-vous est <br> Numéro de rendez-vous : {appointment_unique_id} <br> et le-mail est <br> E-mail de rendez-vous : {appointment_user_email}.</p>
                    <p>Nhésitez pas à nous contacter si vous avez des questions.</p>
                    <p>Merci</p>
                    <p><strong>Salutations,</strong></p>
                    <p><strong>Départements des ressources humaines,</strong></p>
                    <p><strong>{app_name}</strong></p>',

                    'it' => '<p><strong>Caro {appointment_user_name},</strong></p>
                    <p>L ID dell appuntamento è <br> ID appuntamento: {appointment_unique_id} <br> e l e-mail lo è <br> E-mail per l appuntamento: {appointment_user_email}.</p>
                    <p>Sentiti libero di contattarci se hai domande.</p>
                    <p>Grazie</p>
                    <p><strong>Saluti,</strong></p>
                    <p><strong>Dipartimento delle Risorse Umane,</strong></p>
                    <p><strong>{app_name}</strong></p>',

                    'ja' => '<p><strong>親愛なる {appointment_user_name},</strong></p>
                    <p>あなたの予約IDは <br> 予約ID: {appointment_unique_id} <br> そしてメールは <br> 予約メール: {appointment_user_email}.</p>
                    <p>ご質問がございましたら、お気軽にお問い合わせください。</p>
                    <p>ありがとう</p>
                    <p><strong>よろしく、</strong></p>
                    <p><strong>人事部門、</strong></p>
                    <p><strong>{app_name}</strong></p>',

                    'nl' => '<p><strong>Beste {appointment_user_name},</strong></p>
                    <p>Uw afspraak-ID is <br> Afspraak-ID: {appointment_unique_id} <br> en e-mail is <br> Afspraak e-mail: {appointment_user_email}.</p>
                    <p>Neem gerust contact op als u vragen heeft.</p>
                    <p>Bedankt</p>
                    <p><strong>Groeten,</strong></p>
                    <p><strong>HR afdeling,</strong></p>
                    <p><strong>{app_name}</strong></p>',

                    'pl' => '<p><strong>Droga {appointment_user_name},</strong></p>
                    <p>Twój identyfikator spotkania to <br> Identyfikator spotkania: {appointment_unique_id} <br> i e-mail jest <br> E-mail dotyczący spotkania: {appointment_user_email}.</p>
                    <p>Jeśli masz jakiekolwiek pytania, skontaktuj się z nami.</p>
                    <p>Dziękuję</p>
                    <p><strong>Pozdrowienia,</strong></p>
                    <p><strong>Dział Kadr,</strong></p>
                    <p><strong>{app_name}</strong></p>',

                    'pt' => '<p><strong>Querido {appointment_user_name},</strong></p>
                    <p>Seu ID de agendamento é <br> ID do compromisso: {appointment_unique_id} <br> e o e-mail é <br> E-mail de agendamento: {appointment_user_email}.</p>
                    <p>Sinta-se à vontade para entrar em contato se tiver alguma dúvida.</p>
                    <p>Obrigado</p>
                    <p><strong>Cumprimentos,</strong></p>
                    <p><strong>Departamento de Recursos Humanos,</strong></p>
                    <p><strong>{app_name}</strong></p>',

                    'ru' => '<p><strong>Дорогой {appointment_user_name},</strong></p>
                    <p>Ваш идентификатор встречи: <br> Идентификатор встречи: {appointment_unique_id} <br> и электронная почта <br> Электронная почта для записи: {appointment_user_email}.</p>
                    <p>Не стесняйтесь обращаться, если у вас есть какие-либо вопросы..</p>
                    <p>Спасибо</p>
                    <p><strong>С уважением,</strong></p>
                    <p><strong>Отдел кадров,</strong></p>
                    <p><strong>{app_name}</strong></p>',
                ],
            ],
        ];

        foreach ($emailTemplate as $eTemp) {
            $table = EmailTemplate::where('name', $eTemp)->where('module_name', 'Appointment')->exists();
            if (!$table) {
                $emailtemplate = EmailTemplate::create(
                    [
                        'name' => $eTemp,
                        'from' => 'Appointment',
                        'module_name' => 'Appointment',
                        'created_by' => 1,
                        'workspace_id' => 0
                    ]
                );

                foreach ($defaultTemplate[$eTemp]['lang'] as $lang => $content) {
                    EmailTemplateLang::create(
                        [
                            'parent_id' => $emailtemplate->id,
                            'lang' => $lang,
                            'subject' => $defaultTemplate[$eTemp]['subject'],
                            'variables' => $defaultTemplate[$eTemp]['variables'],
                            'content' => $content,
                        ]
                    );
                }
            }
        }
    }
}
