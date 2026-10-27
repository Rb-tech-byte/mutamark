<?php

namespace Workdo\Fleet\Database\Seeders;

use App\Models\EmailTemplate;
use App\Models\EmailTemplateLang;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class EmailTemplateTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $emailTemplate = [
            'New Vehicle',
            'New Booking',
            'New Booking Payment',
        ];

        $defaultTemplate = [
            'New Vehicle' => [
                'subject' => 'New Vehicle',
                'variables' => '{
                    "Vehicle Name": "vehicle_name",
                    "Driver Name": "driver_name",
                    "Vehicle Type": "vehicle_type",
                    "App Url": "app_url",
                    "App Name": "app_name",
                    "Company Name ":"company_name",
                    "Email" : "email"
                  }',
                'lang' => [
                    'ar' => '<p><span style="font-family: sans-serif;">مرحبا ،</span></p> <p><span style="font-family: sans-serif;"><span style="font-family: Inter, sans-serif;">مرحبا بك في { app_name }</span></span></p></p> <p><br style="font-family: sans-serif;" /><span style="font-family: sans-serif;">مركبة جديدة تم تخصيصها لك.</span></p> <p><span style="font-family: sans-serif;"><strong>اسم المركبات</strong> : { المركبة _ المركبة }<br /><strong>نوع المركبات</strong> : { birle_type }<br /><strong>اسم المشغل</strong> : { driver_name }<br /></span></p> <p>{ app_url }</p> <p></p> <p>شكرا ،<br />{ app_name }</p>',
                    'da' => '<p><span style="font-family: sans-serif;">Hallo,</span></p> <p><span style="font-family: sans-serif;"><span style="font-family: Inter, sans-serif;">Velkommen til { app_navn }</span></span></p> <p><br style="font-family: sans-serif;" /><span style="font-family: sans-serif;">Nyt køretøj er blevet tilknyt til dig.</span></p> <p><span style="font-family: sans-serif;"><strong>Navn på køretøj</strong> : { vehicle_name }<br /><strong>Køretøjstype</strong> : { vehicle_type }<br /><strong>Drivernavn</strong> : { driver_name }<br /></span></p> <p>{ app_url }</p> <p>&nbsp;</p> <p>Tak,<br />{ app_navn }</p>',
                    'de' => '<p><span style="font-family: sans-serif;">Hello,</span></p> <p><span style="font-family: sans-serif;"><span style="font-family: Inter, sans-serif;">Willkommen bei {app_name}</span></span></p> <p><br style="font-family: sans-serif;" /><span style="font-family: sans-serif;">Neues Fahrzeug wurde Ihnen zugeordnet.</span></p> <p><span style="font-family: sans-serif;"><strong>Fahrzeugname</strong> : {vehicle_name}<br /><strong>Fahrzeugtyp</strong> : {vehicle_type}<br /><strong>Treibername</strong> : {driver_name}<br /></span></p> <p>{app_url}</p> <p>&nbsp;</p> <p>Danke,<br />{app_name}</p>',
                    'en' => '<p><span style="font-family: sans-serif;">Hello,</span></p>
                    <p><span style="font-family: sans-serif;"><span style="font-family: Inter, sans-serif;">Welcome to {app_name}</span></span></p>
                    <p><br style="font-family: sans-serif;" /><span style="font-family: sans-serif;">New Vehicle has been Assign to you.</span></p>
                    <p><span style="font-family: sans-serif;"><strong>Vehicle Name</strong> : {vehicle_name}<br /><strong>Vehicle Type</strong> : {vehicle_type}<br /><strong>Driver Name</strong> : {driver_name}<br /></span></p>
                    <p>{app_url}</p>
                    <p>&nbsp;</p>
                    <p>Thanks,<br />{app_name}</p>',
                    'es' => '<p><span style="font-family: sans-serif;">Hola,</span></p> <p><span style="font-family: sans-serif;"><span style="font-family: Inter, sans-serif;"><span style="font-family: Inter, sans-serif;">a {app_name}</span></span></p> <p><br style="font-family: sans-serif;" /><span style="font-family: sans-serif;">Nuevo vehículo se ha asignado a usted.</span></p> <p><span style="font-family: sans-serif;"><strong><strong>del vehículo</strong> : {vehicle_name}<br /><strong>Tipo de vehículo</strong> : {vehicle_type}<br /><strong>Nombre del controlador</strong> : {driver_name}<br /></span></p> <p>{app_url}</p> <p>&nbsp;</p> <p>Gracias,<br />{app_name}</p>',
                    'fr' => '<p><span style="font-family: sans-serif;">Bonjour,</span></p> <p><span style="font-family: sans-serif;"><span style="font-family: Inter, sans-serif;">Bienvenue dans { app_name }</span></span></p> <p><br style="font-family: sans-serif;" /><span style="font-family: sans-serif;">New Vehicle has been Assign to you.</span></p> <p><span style="font-family: sans-serif;"><strong>Nom du véhicule</strong> : { véhiculle_name }<br /><strong>Type de véhicule</strong> : { véhiculle_type }<br /><strong>Nom du pilote</strong> : { driver_name }<br /></span></p> <p>{ app_url }</p> <p>&nbsp;</p> <p>Merci,<br />{ app_name }</p>',
                    'it' => '<p><span style="font-family: sans-serif;">Ciao,</span></p> <p><span style="font-family: sans-serif;"><span style="font-family: Inter, sans-serif;">Benvenuti in {app_name}</span></span></p></p> <p><br style="font-family: sans-serif;" /><span style="font-family: sans-serif;">Nuovo veicolo è stato Assign to you.</span></p> <p><span style="font-family: sans-serif;"><strong>Nome del veicolo</strong> : {veicolo_veicolare}<br /><strong>Tipo di veicolo</strong> : {veicolo_tipo}<br /><strong>Nome driver</strong> : {driver_name}<br /></span></p> <p>{app_url}</p> <p>&nbsp;</p> <p>Grazie,<br />{app_name}</p>',
                    'ja' => '<p><span style="font-family: sans-serif;">こんにちは</span></p> <p><span style="font-family: sans-serif;"><span style="font-family: Inter, sans-serif;"><span style="font-family: Inter, sans-serif;">app_name}</span>ようこそ</span></p></p></p> <p><br style="font-family: sans-serif;" /><span style="font-family: sans-serif;">新しい車両がユーザーに割り当てられています。</span></p> <p><strong><strong>車両名</strong> : {車両名</strong><br /><strong>車両タイプ</strong> : {車両タイプ}<br /><strong>ドライバー名</strong> : {driver_name}<br /></span></p> <p>{app_url}</p> <p>&nbsp;</p> <p>ありがとうございます、<br />{app_name}</p>',
                    'nl' => '<p><span style="font-family: sans-serif;">Hallo,</span></p>
                    <p><span style="font-family: sans-serif;"><span style="font-family: Inter, sans-serif;">Welkom bij { app_name }</span></span></p>
                    </p>
                    <br style="font-family: sans-serif;" /><span style="font-family: sans-serif;">Nieuw voertuig is aan u toegewezen.</span></p>
                    <p><span style="font-family: sans-serif;"><strong>Voertuignaam</strong> : { voertuignaam }<br /><strong>Voertuigtype</strong> : { vehicle_type }<br /><strong>Stuurprogrammanaam</strong> : { driver_name }<br /></span></p>
                    <p>{ app_url }</p>
                    <p>&nbsp;</p>
                    <p>Bedankt,<br />{ app_name }</p>',
                    'pl' => '<p><span style="font-family: sans-serif;">Hello,</span></p> <p><span style="font-family: sans-serif;"><span style="font-family: Inter, sans-serif;">Witamy w aplikacji {app_name }</span></span></p> <p><br style="font-family: sans-serif;" /><span style="font-family: sans-serif;">Nowy pojazd został przypisany do użytkownika.</span></p> <p><span style="font-family: sans-serif;"><strong>Nazwa pojazdu</strong> : {vehicle_name }<br /><strong>typ pojazdu</strong> : {vehicle_type }<br /><strong>Nazwa sterownika</strong> : {driver_name }<br /></span></p> <p>{app_url }</p> <p>&nbsp;</p> <p>Thanks,<br />{app_name }</p>',
                    'ru' => '<p><span style="font-family: sans-serif;">Hello,</span></p>
                    <p><span style="font-family: sans-serif;"><span style="font-family: Inter, sans-serif;">Добро пожаловать в { app_name }</span></span></p>
                    <p><br style="font-family: sans-serif;" /><span style="font-family: sans-serif;">Новое транспортное средство было присвоили вам.</span></p>
                    <p><span style="font-family: sans-serif;"><strong>Имя транспортного средства</strong> : { vehicle_name }<br /><strong>Тип транспортного средства</strong> : { vehicle_type }<br /><strong>Имя драйвера</strong> : { driver_name }<br /></span></p>
                    <p>{ app_url }</p>
                    <p>&nbsp;</p>
                    <p>Спасибо,<br />{ app_name }</p>',
                    'pt' => '<p><span style="font-family: sans-serif;">Olá,</span></p> <p><span style="font-family: sans-serif;"><span style="font-family: Inter, sans-serif;">Bem-vindo a {app_name}</span></span></p> <p><br style="font-family: sans-serif;" /><span style="font-family: sans-serif;">Novo Veículo foi Designar para você.</span></p> <p><span style="font-family: sans-serif;"><strong>Nome do Veículo</strong> : {veíle_nome__veículo}<br /><strong>Tipo de Veículo</strong> : {veílele_type}<br /><strong>Nome do Driver</strong> : {driver_name}<br /></span></p> <p>{app_url}</p> <p>&nbsp;</p> <p>Obrigado,<br />{app_name}</p>',
                ],
            ],
            'New Booking' => [
                'subject' => 'New Booking',
                'variables' => '{
                    "Customer Name": "customer_name",
                    "Driver Name": "driver_name",
                    "Total Price": "total_price",
                    "App Url": "app_url",
                    "App Name": "app_name",
                    "Company Name ":"company_name",
                    "Email" : "email"
                  }',
                  'lang' => [
                    'ar' => '<p>مرحبا ،<br />مرحبا بك في { app_name }</p>
                    <p>حجز جديد</p>
                    <p><span style="font-family: sans-serif;"><strong>اسم العميل</strong> : { customer_name }</span><br /><strong style="font-family: sans-serif;">اسم برنامج التشغيل</strong><span style="font-family: sans-serif;">: { driver_name }</span></p>
                    <p><strong style="font-family: sans-serif;">اجمالي السعر</strong><span style="font-family: sans-serif;"> : { total_Price }</span></p>
                    <p>{ app_url }</p>
                    <p>شكرا ،<br />{ app_name }</p>
                    <p><span style="font-family: sans-serif;"></span></p>',
                    'da' => '<p>Hallo, &nbsp;<br />Velkommen til { app_name } &nbsp;</p>
                    <p>Ny booking &nbsp;</p>
                    <p><span style="font-family: sans-serif;"><strong>Kundenavn</strong> : { customer_name }</span></span><br /><strong style="font-family: sans-serif;">Styreprogramnavn</strong><span style="font-family: sans-serif;">&nbsp;: { driver_name }</span></p>
                    <p><strong style="font-family: sans-serif;">Total pris</strong><span style="font-family: sans-serif;"> : { total_price }</span></p>
                    <p>{ app_url }</p>
                    <p>Tak,<br />{ app_name }</p>
                    <p><span style="font-family: sans-serif;">&nbsp;</span></p>',
                    'de' => '<p>Hello, &nbsp;<br />Willkommen bei {app_name} &nbsp;</p>
                    <p>Neue Buchung &nbsp;</p>
                    <p> <p><span style="font-family: sans-serif;"><strong>Kundenname</strong> : {customer_name}</span><br /><strong style="font-family: sans-serif;">Treibername</strong><span style="font-family: sans-serif;">&nbsp;: {driver_name}</span></p>
                    <p><strong style="font-family: sans-serif;">Gesamtpreis</strong><span style="font-family: sans-serif;"> : {total_price}</span></p>
                    <p>{app_url}</p>
                    <p>Danke,<br />{Anwendungsname}</p>
                    <p><span style="font-family: sans-serif;">&nbsp;</span></p>',
                    'en' => '<p>Hello,&nbsp;<br />Welcome to {app_name}&nbsp;</p>
                    <p>New Booking&nbsp;</p>
                    <p><span style="font-family: sans-serif;"><strong>Customer Name</strong> : {customer_name}</span><br /><strong style="font-family: sans-serif;">Driver Name</strong><span style="font-family: sans-serif;">&nbsp;: {driver_name}</span></p>
                    <p><strong style="font-family: sans-serif;">Total Price</strong><span style="font-family: sans-serif;"> : {total_price}</span></p>
                    <p>{app_url}</p>
                    <p>Thanks,<br />{app_name}</p>
                    <p><span style="font-family: sans-serif;">&nbsp;</span></p>',
                    'es' => '<p>Hola, &nbsp;<br />Bienvenido a {app_name} &nbsp;</p>
                    <p>Nueva reserva &nbsp;</p>
                    <p> <p><span style="font-family: sans-serif;"><strong><strong>del cliente</strong> : {customer_name}</span><br /><strong style="font-family: sans-serif;">Nombre del controlador</strong><span style="font-family: sans-serif;">&nbsp;: {driver_name}</span></p>
                    <p><strong style="font-family: sans-serif;">Precio total</strong><span style="font-family: sans-serif;"> : {total_price}</span></p>
                    <p>{app_url}</p>
                    <p>Gracias,<br />{app_name}</p>
                    <p><span style="font-family: sans-serif;">&nbsp;</span></p>',
                    'fr' => '<p>Bonjour, &nbsp;<br />Bienvenue dans { app_name } &nbsp;</p>
                    <p>Nouvelle réservation &nbsp;</p>
                    <p><span style="font-family: sans-serif;"><strong>Nom du client</strong> : { nom_client }</span><br /><strong style="font-family: sans-serif;">Nom du pilote</strong><span style="font-family: sans-serif;">&nbsp;: { nom_unité }</span></p>
                    <p><strong style="font-family: sans-serif;">Prix total</strong><span style="font-family: sans-serif;"> : { total_price }</span></p>
                    <p>{ adresse_url }</p>
                    <p>Merci,<br />{ app_name }</p>
                    <p><span style="font-family: sans-serif;">&nbsp;</span></p>',
                    'it' => '<p>Ciao, &nbsp;<br />Benvenuti in {app_name} &nbsp;</p>
                    <p>Nuovo Booking &nbsp;</p>
                    <p><span style="font-family: sans-serif;"><strong>Nome cliente</strong> : {customer_name}</span><br /><strong style="font-family: sans-serif;">Nome driver</strong><span style="font-family: sans-serif;">&nbsp;: {driver_name}</span></p>
                    <p><strong style="font-family: sans-serif;">Prezzo totale</strong><span style="font-family: sans-serif;"> : {total_price}</span></p>
                    <p>{app_url}</p>
                    <p>Grazie,<br />{app_name}</p>
                    <p><span style="font-family: sans-serif;">&nbsp;</span></p>',
                    'ja' => '<p>「こんにちは &nbsp;<br />、「{ app_name}&nbsp;へようこそ。</p>
                    <p>新規予約&nbsp;</p>
                    <p><span style="font-family: sans-serif;"><strong><strong>名</strong> : {customer_name}</span><br /><strong style="font-family: sans-serif;">ドライバー名</strong><span style="font-family: sans-serif;">&nbsp;: {driver_name}</span></p>
                    <p><strong style="font-family: sans-serif;">合計価格</strong><span style="font-family: sans-serif;"> : {total_対価}</span></p>
                    <p>{app_url}</p>
                    <p>ありがとう<br />。<br />{app_name}</p>
                    <p><span style="font-family: sans-serif;">&nbsp;</span></p>',
                    'nl' => '<p>Hallo, &nbsp;<br />Welkom bij { app_name } &nbsp;</p>
                    <p>Nieuwe boeking &nbsp;</p>
                    <p><span style="font-family: sans-serif;"><strong>Klantnaam</strong> : { customer_name }</span><br /><strong style="font-family: sans-serif;">Naam stuurprogramma</strong><span style="font-family: sans-serif;">&nbsp;: { driver_name }</span></p>
                    <p><strong style="font-family: sans-serif;">Totale prijs</strong><span style="font-family: sans-serif;"> : { total_price }</span></p>
                    <p>{ app_url }</p>
                    <p>Bedankt,<br />{ app_name }</p>
                    <p><span style="font-family: sans-serif;">&nbsp;</span></p>',
                    'pl' => '<p>Hello, &nbsp;<br />Witamy w aplikacji {app_name } &nbsp;</p>
                    <p>Nowe rezerwacje &nbsp;</p>
                    <p> <p><span style="font-family: sans-serif;"><strong>Nazwa klienta</strong> : {customer_name }</span><br /><strong style="font-family: sans-serif;">Nazwa sterownika</strong><span style="font-family: sans-serif;">&nbsp;: {driver_name }</span></p>
                    <p><strong style="font-family: sans-serif;">Cena łącznie</strong><span style="font-family: sans-serif;"> : {total_price }</span></p>
                    <p>{app_url }</p>
                    <p>Dzięki,<br />{app_name }</p>
                    <p><span style="font-family: sans-serif;">&nbsp;</span></p>',
                    'ru' => '<p>Здравствуйте, &nbsp;<br />Добро пожаловать в { app_name } &nbsp;</p>
                    <p>Новое бронирование &nbsp;</p>
                    <p><span style="font-family: sans-serif;"><strong>Имя клиента</strong> : { customer_name }</span><br /><strong style="font-family: sans-serif;">Имя драйвера</strong><span style="font-family: sans-serif;">&nbsp;: { driver_name }</span></p>
                    <p><strong style="font-family: sans-serif;">Общая цена</strong><span style="font-family: sans-serif;"> : { total_price }</span></p>
                    <p>{ app_url }</p>
                    <p>Спасибо,<br />{ имя_программы }</p>
                    <p><span style="font-family: sans-serif;">&nbsp;</span></p>',
                    'pt' => '<p>Olá, &nbsp;<br />Bem-vindo a {app_name} &nbsp;</p>
                    <p>Novo Booking &nbsp;</p>
                    <p><span style="font-family: sans-serif;"><strong>Nome do Cliente</strong> : {customer_name}</span><br /><strong style="font-family: sans-serif;">Nome do Driver</strong><span style="font-family: sans-serif;">&nbsp;: {driver_name}</span></p>
                    <p><strong style="font-family: sans-serif;">Preço Total</strong><span style="font-family: sans-serif;"> : {total_price}</span></p>
                    <p>{app_url}</p>
                    <p>Obrigado,<br />{app_name}</p>
                    <p><span style="font-family: sans-serif;">&nbsp;</span></p>',
                ],
            ],
            'New Booking Payment' => [
                'subject' => 'New Booking Payment',
                'variables' => '{
                    "Customer Name": "customer_name",
                    "Pay Amount": "pay_amount",
                    "Total Payment": "total_payment",
                    "App Url": "app_url",
                    "Company Name ":"company_name",
                    "App Name": "app_name"
                    }',
                'lang' => [
                    'ar' => '<p>مرحبا ،<br />مرحبا بك في { app_name }</p>
                    <p>الدفعات الخاصة بالحجز</p>
                    <p><span style="font-family: sans-serif;"><strong>اسم العميل</strong> : { customer_name }</span><br /><strong style="font-family: sans-serif;">قيمة الدفع</strong><span style="font-family: sans-serif;"> : { pay_cالكمية }</span></p>
                    <p><strong style="font-family: sans-serif;">اجمالي المدفوعات</strong><span style="font-family: sans-serif;"> : { total_Payment }</span></p>
                    <p>{ app_url }</p>
                    <p>شكرا ،<br />{ app_name }</p>
                    <p><span style="font-family: sans-serif;"></span></p>',
                    'da' => '<p>Hallo, &nbsp;<br />Velkommen til { app_name } &nbsp;</p>
                    <p>Booking Payment</p>
                    <p><span style="font-family: sans-serif;"><strong>Kundenavn</strong> : { customer_name }</span><br /><strong style="font-family: sans-serif;">Betalingsbeløb</strong><span style="font-family: sans-serif;"> : { pay_amount }</span></p>
                    <p><strong style="font-family: sans-serif;">Betaling i alt</strong><span style="font-family: sans-serif;"> : { total_payment }</span></p>
                    <p>{ app_url }</p>
                    <p>Tak,<br />{ app_name }</p>
                    <p><span style="font-family: sans-serif;">&nbsp;</span></p>',
                    'de' => '<p>Hello, &nbsp;<br />Willkommen bei {app_name} &nbsp;</p>
                    <p>Zahlung buchen</p>
                    <p> <p><span style="font-family: sans-serif;"><strong>Kundenname</strong> : {customer_name}</span><br /><strong style="font-family: sans-serif;">Pay Amount</strong><span style="font-family: sans-serif;"> : {pay_amount}</span></p>
                    <p><strong style="font-family: sans-serif;">Gesamtzahlung</strong><span style="font-family: sans-serif;"> : {total_payment}</span></p>
                    <p>{app_url}</p>
                    <p>Danke,<br />{Anwendungsname}</p>
                    <p><span style="font-family: sans-serif;">&nbsp;</span></p>',
                    'en' => '<p>Hello,&nbsp;<br />Welcome to {app_name}&nbsp;</p>
                    <p>Booking Payment</p>
                    <p><span style="font-family: sans-serif;"><strong>Customer Name</strong> : {customer_name}</span><br /><strong style="font-family: sans-serif;">Pay Amount</strong><span style="font-family: sans-serif;"> : {pay_amount}</span></p>
                    <p><strong style="font-family: sans-serif;">Total Payment</strong><span style="font-family: sans-serif;"> : {total_payment}</span></p>
                    <p>{app_url}</p>
                    <p>Thanks,<br />{app_name}</p>
                    <p><span style="font-family: sans-serif;">&nbsp;</span></p>',
                    'es' => '<p>Hola, &nbsp;<br />Bienvenido a {app_name} &nbsp;</p>
                    <p>Pago de reserva</p>
                    <p> <p><span style="font-family: sans-serif;"><strong><strong>del cliente</strong> : {customer_name}</span><br /><strong style="font-family: sans-serif;">Importe de pago</strong><span style="font-family: sans-serif;"> : {pay_amount}</span></p>
                    <p><strong style="font-family: sans-serif;">Pago total</strong><span style="font-family: sans-serif;"> : {total_payment}</span></p>
                    <p>{app_url}</p>
                    <p>Gracias,<br />{app_name}</p>
                    <p><span style="font-family: sans-serif;">&nbsp;</span></p>',
                    'fr' => '<p>Bonjour, &nbsp;<br />Bienvenue dans { app_name } &nbsp;</p>
                    <p>Paiement de réservation</p>
                    <p><span style="font-family: sans-serif;"><strong>Nom du client</strong> : { nom_client }</span><br /><strong style="font-family: sans-serif;">Montant de la paye</strong><span style="font-family: sans-serif;"> : { pay_amount }</span></p>
                    <p><strong style="font-family: sans-serif;">Paiement total</strong><span style="font-family: sans-serif;"> : { total_payment }</span></p>
                    <p>{ adresse_url }</p>
                    <p>Merci,<br />{ app_name }</p>
                    <p><span style="font-family: sans-serif;">&nbsp;</span></p>',
                    'it' => '<p>Ciao, &nbsp;<br />Benvenuti in {app_name} &nbsp;</p>
                    <p>Pagamento prenotazione</p>
                    <p><span style="font-family: sans-serif;"><strong>Nome cliente</strong> : {customer_name}</span><br /><strong style="font-family: sans-serif;">Quantità di vassoio</strong><span style="font-family: sans-serif;"> : {pay_amount}</span></p></p>
                    <p><strong style="font-family: sans-serif;">Pagamento totale</strong><span style="font-family: sans-serif;"> : {total_payment}</span></p>
                    <p>{app_url}</p>
                    <p>Grazie,<br />{app_name}</p>
                    <p><span style="font-family: sans-serif;">&nbsp;</span></p>',
                    'ja' => '<p>「こんにちは &nbsp;<br />、「{ app_name}&nbsp;へようこそ。</p>
                    <p>支払いの予約</p>
                    <p><span style="font-family: sans-serif;"><strong><strong>名</strong> : {customer_name}</span><br /><strong style="font-family: sans-serif;">支払金額</strong><span style="font-family: sans-serif;"> : {pay_amount}</span></p>
                    <p><strong style="font-family: sans-serif;">支払い合計</strong><span style="font-family: sans-serif;"> : {total_弁済}</span></p>
                    <p>{app_url}</p>
                    <p>ありがとう<br />。<br />{app_name}</p>
                    <p><span style="font-family: sans-serif;">&nbsp;</span></p>',
                    'nl' => '<p>Hallo, &nbsp;<br />Welkom bij { app_name } &nbsp;</p>
                    <p>Boekingsbetaling</p>
                    <p><span style="font-family: sans-serif;"><strong>Klantnaam</strong> : { customer_name }</span><br /><strong style="font-family: sans-serif;">Betonbedrag</strong><span style="font-family: sans-serif;"> : { pay_amount }</span></p>
                    <p><strong style="font-family: sans-serif;">Totale betaling</strong><span style="font-family: sans-serif;"> : { total_payment }</span></p>
                    <p>{ app_url }</p>
                    <p>Bedankt,<br />{ app_name }</p>
                    <p><span style="font-family: sans-serif;">&nbsp;</span></p>',
                    'pl' => '<p>Hello, &nbsp;<br />Witamy w aplikacji {app_name } &nbsp;</p>
                    <p>Rezerwacja płatności</p>
                    <p> <p><span style="font-family: sans-serif;"><strong>Nazwa klienta</strong> : {customer_name }</span><br /><strong style="font-family: sans-serif;">Kwota płatności</strong><span style="font-family: sans-serif;"> : {pay_amount }</span></p>
                    <p><strong style="font-family: sans-serif;">Łączna płatność</strong><span style="font-family: sans-serif;"> : {total_payment }</span></p>
                    <p>{app_url }</p>
                    <p>Dzięki,<br />{app_name }</p>
                    <p><span style="font-family: sans-serif;">&nbsp;</span></p>',
                    'ru' => '<p>Здравствуйте, &nbsp;<br />Добро пожаловать в { app_name } &nbsp;</p>
                    <p>Бронирование платежа</p>
                    <p><span style="font-family: sans-serif;"><strong>Имя клиента</strong> : { customer_name }</span><br /><strong style="font-family: sans-serif;">Сумма оплаты</strong><span style="font-family: sans-serif;"> : { pay_сумма }</span></p>
                    <p><strong style="font-family: sans-serif;">Общий платеж</strong><span style="font-family: sans-serif;"> : { total_payment }</span></p>
                    <p>{ app_url }</p>
                    <p>Спасибо,<br />{ имя_программы }</p>
                    <p><span style="font-family: sans-serif;">&nbsp;</span></p>',
                    'pt' => '<p>Olá, &nbsp;<br />Bem-vindo a {app_name} &nbsp;</p>
                    <p>Pagamento de Booking</p>
                    <p><span style="font-family: sans-serif;"><strong>Nome do Cliente</strong> : {customer_name}</span><br /><strong style="font-family: sans-serif;">Pagamento da Quantia</strong><span style="font-family: sans-serif;"> : {pay_amount}</span></p>
                    <p><strong style="font-family: sans-serif;">Pagamento Total</strong><span style="font-family: sans-serif;"> : {total_payment}</span></p>
                    <p>{app_url}</p>
                    <p>Obrigado,<br />{app_name}</p>
                    <p><span style="font-family: sans-serif;">&nbsp;</span></p>',
                ],
            ],

        ];

        foreach ($emailTemplate as $eTemp) {
            $table = EmailTemplate::where('name', $eTemp)->where('module_name', 'Fleet')->exists();
            if (!$table) {
                $emailtemplate =  EmailTemplate::create(
                    [
                        'name' => $eTemp,
                        'from' => 'Fleet',
                        'module_name' => 'Fleet',
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

        // $this->call("OthersTableSeeder");
    }
}
