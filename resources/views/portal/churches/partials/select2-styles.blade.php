{{-- Select2 look shared by the church pages (management + hierarchy). --}}
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/select2.css') }}">
<style>
    .select2-container .select2-selection--single {
        height: 42px !important;
        border: 1px solid #e2e6ee !important;
        border-radius: 8px !important;
        display: flex;
        align-items: center;
        padding: 0 6px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: normal; padding-left: 6px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px; }
    .select2-container--default .select2-selection--single:focus,
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #4d7de0 !important;
        box-shadow: 0 0 0 3px rgba(77,125,224,.15);
    }
    .select2-dropdown { border-radius: 8px; border-color: #e2e6ee; overflow: hidden; }
    .select2-search--dropdown .select2-search__field { border-radius: 6px; border: 1px solid #e2e6ee; padding: 6px 8px; }
</style>
