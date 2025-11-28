@extends('shop.layout.app')
@section('title', 'Privacy Policy')
@section('style')
    <link href="{{ asset('inspinia/css/plugins/blueimp/css/blueimp-gallery.min.css') }}" rel="stylesheet">
    <style>
        .lightBoxGallery {
            text-align: center;
        }

        .lightBoxGallery img {
            margin: 5px;
        }
    </style>
@endsection
@section('content')
    <div class="container clearfix">

        <div class="fancy-title title-border mb-4 title-center">
            <h4>Privacy Policy</h4>
        </div>

        <p>Privacy policy atau kebijakan privasi yang dimaksud di MG Network adalah acuan yang mengatur dan
            melindungi penggunaan data dan informasi
            penting para Pengguna MG Network. Data dan informasi yang telah dikumpulkan pada saat mendaftar,
            mengakses, dan
            menggunakan layanan di MG Network, seperti alamat, nomor kontak, alamat e-mail, foto, gambar, dan
            lain-lain.</p>
        <p>Kebijakan-kebijakan tersebut di antaranya:</p>
        <ul>
            <li>MG Network tunduk terhadap kebijakan perlindungan data pribadi Pengguna sebagaimana yang diatur
                dalam Peraturan
                Menteri Komunikasi dan Informatika Nomor 20 Tahun 2016 Tentang Perlindungan Data Pribadi Dalam Sistem
                Elektronik
                yang mengatur dan melindungi penggunaan data dan informasi penting para Pengguna.</li>
            <li>MG Network melindungi segala informasi yang diberikan Pengguna pada saat pendaftaran, mengakses,
                dan menggunakan
                seluruh layanan MG Network.</li>
            <li>MG Network melindungi segala hak pribadi yang muncul atas informasi mengenai suatu produk yang
                ditampilkan oleh
                pengguna layanan MG Network, baik berupa foto, username, logo, dan lain-lain.</li>
            <li>MG Network berhak menggunakan data dan informasi para Pengguna demi meningkatkan mutu dan
                pelayanan di MG Network.</li>
            <li>MG Network tidak bertanggung jawab atas pertukaran data yang dilakukan sendiri di antara Pengguna.
            </li>
            <li>MG Network hanya dapat memberitahukan data dan informasi yang dimiliki oleh para Pengguna bila
                diwajibkan dan/atau
                diminta oleh institusi yang berwenang berdasarkan ketentuan hukum yang berlaku, perintah resmi dari
                Pengadilan,
                dan/atau perintah resmi dari instansi atau aparat yang bersangkutan.</li>
        </ul>

    </div>
@endsection
@section('script')
    <!-- blueimp gallery -->
    <script src="{{ asset('inspinia/js/plugins/blueimp/jquery.blueimp-gallery.min.js') }}"></script>
@endsection
