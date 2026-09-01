<?php

namespace App\Filament\Pages;

use App\Models\AppSetting;
use BackedEnum;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use UnitEnum;

/**
 * Single screen where an admin controls the white-label identity and the
 * attendance rules that the mobile app reads from /api/app-settings.
 */
class PengaturanAplikasi extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static UnitEnum|string|null $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 90;

    protected static ?string $navigationLabel = 'Pengaturan Aplikasi';

    protected static ?string $title = 'Pengaturan Aplikasi';

    protected string $view = 'filament.pages.pengaturan-aplikasi';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public function mount(): void
    {
        $this->form->fill(AppSetting::current()->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Tabs::make('settings')
                    ->columnSpanFull()
                    ->tabs([
                        Tabs\Tab::make('Identitas')
                            ->icon('heroicon-o-identification')
                            ->schema([
                                Section::make('Nama Aplikasi')
                                    ->description('Ditampilkan pada splash screen, login dan header aplikasi mobile.')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('app_name')
                                            ->label('Nama Aplikasi')
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('app_short_name')
                                            ->label('Nama Singkat')
                                            ->maxLength(255)
                                            ->helperText('Dipakai jika ruang tampilan terbatas.'),
                                        TextInput::make('tagline')
                                            ->label('Tagline')
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Logo & Warna')
                                    ->columns(2)
                                    ->schema([
                                        FileUpload::make('logo_path')
                                            ->label('Logo Terang')
                                            ->image()
                                            ->disk('public')
                                            ->directory('branding')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->maxSize(2048),
                                        FileUpload::make('logo_dark_path')
                                            ->label('Logo Gelap')
                                            ->image()
                                            ->disk('public')
                                            ->directory('branding')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->maxSize(2048),
                                        FileUpload::make('favicon_path')
                                            ->label('Favicon')
                                            ->image()
                                            ->disk('public')
                                            ->directory('branding')
                                            ->visibility('public')
                                            ->maxSize(1024),
                                        FileUpload::make('login_banner_path')
                                            ->label('Banner Halaman Login')
                                            ->image()
                                            ->disk('public')
                                            ->directory('branding')
                                            ->visibility('public')
                                            ->maxSize(2048),
                                        ColorPicker::make('primary_color')
                                            ->label('Warna Utama')
                                            ->required(),
                                        ColorPicker::make('secondary_color')
                                            ->label('Warna Sekunder'),
                                    ]),
                            ]),

                        Tabs\Tab::make('Perusahaan')
                            ->icon('heroicon-o-building-office-2')
                            ->schema([
                                Section::make('Kontak')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('company_name')->label('Nama Perusahaan')->maxLength(255),
                                        TextInput::make('website')->label('Website')->url()->maxLength(255),
                                        TextInput::make('support_email')->label('Email Bantuan')->email()->maxLength(255),
                                        TextInput::make('support_phone')->label('Telepon Bantuan')->tel()->maxLength(32),
                                        Textarea::make('address')->label('Alamat')->rows(3)->columnSpanFull(),
                                    ]),
                            ]),

                        Tabs\Tab::make('Presensi')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                Section::make('Aturan Presensi')
                                    ->description('Aturan ini dikirim ke aplikasi mobile dan divalidasi ulang di server.')
                                    ->columns(2)
                                    ->schema([
                                        Toggle::make('attendance_photo_required')
                                            ->label('Wajib foto untuk semua mode')
                                            ->helperText('Berlaku untuk WFO, WFH dan WFA.'),
                                        Toggle::make('block_mock_location')
                                            ->label('Tolak lokasi palsu (fake GPS)'),
                                        Toggle::make('wfh_enabled')
                                            ->label('Aktifkan presensi WFH/WFA')
                                            ->live(),
                                        Toggle::make('wfh_photo_required')
                                            ->label('Wajib foto saat WFH/WFA')
                                            ->visible(fn ($get): bool => (bool) $get('wfh_enabled')),
                                        Toggle::make('wfh_notes_required')
                                            ->label('Wajib catatan aktivitas saat WFH/WFA')
                                            ->visible(fn ($get): bool => (bool) $get('wfh_enabled')),
                                        TextInput::make('location_accuracy_tolerance_meters')
                                            ->label('Toleransi akurasi GPS (meter)')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(1000)
                                            ->required(),
                                    ]),

                                Section::make('Peta')
                                    ->description('Peta yang ditampilkan sebelum pegawai menekan tombol presensi.')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('map_default_zoom')
                                            ->label('Zoom Awal')
                                            ->numeric()
                                            ->minValue(1)
                                            ->maxValue(20)
                                            ->required(),
                                        TextInput::make('map_tile_url')
                                            ->label('URL Tile Peta')
                                            ->placeholder('https://tile.openstreetmap.org/{z}/{x}/{y}.png')
                                            ->maxLength(255),
                                        TextInput::make('map_attribution')
                                            ->label('Atribusi Peta')
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Tabs\Tab::make('Versi & Pemeliharaan')
                            ->icon('heroicon-o-wrench-screwdriver')
                            ->schema([
                                Section::make('Versi Aplikasi Mobile')
                                    ->schema([
                                        Grid::make(4)->schema([
                                            TextInput::make('android_latest_version')->label('Android Terbaru')->maxLength(20),
                                            TextInput::make('android_min_version')->label('Android Minimum')->maxLength(20),
                                            TextInput::make('ios_latest_version')->label('iOS Terbaru')->maxLength(20),
                                            TextInput::make('ios_min_version')->label('iOS Minimum')->maxLength(20),
                                        ]),
                                        Toggle::make('force_update')
                                            ->label('Paksa pembaruan aplikasi'),
                                    ]),

                                Section::make('Mode Pemeliharaan')
                                    ->description('Saat aktif, presensi lewat API ditolak dengan pesan di bawah ini.')
                                    ->schema([
                                        Toggle::make('maintenance_mode')
                                            ->label('Aktifkan mode pemeliharaan')
                                            ->live(),
                                        Textarea::make('maintenance_message')
                                            ->label('Pesan Pemeliharaan')
                                            ->rows(3)
                                            ->visible(fn ($get): bool => (bool) $get('maintenance_mode')),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        AppSetting::current()->update($data);
        AppSetting::flushCache();

        Notification::make()
            ->title('Pengaturan aplikasi disimpan')
            ->body('Perubahan langsung dibaca oleh aplikasi mobile melalui /api/app-settings.')
            ->success()
            ->send();
    }
}
