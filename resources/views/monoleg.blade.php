@extends('layout.app')
@section('title', 'Monoleg')
@section('style')
    <link href="{{ asset('material-pro/assets/plugins/select2/dist/css/select2.min.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('assets/tree/style.css') }}" rel="stylesheet">
@endsection
@php
@endphp
@section('content')
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-5 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">Monoleg</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="javascript:void(0)">Home</a>
                    </li>
                    <li class="breadcrumb-item active">Monoleg</li>
                </ol>
            </div>
            <div class="col-md-7 col-4 align-self-center">
                <div class="d-flex m-t-10 justify-content-end">
                </div>
            </div>
        </div>
        @if (auth()->user()->type == 'admin')
            <div class="form-group">
                <select id="users" style="width: 100%;"></select>
            </div>
        @endif
        <div class="card mb-0"
            style="{{ auth()->user()->type == 'admin' ? 'height: calc(100vh - 334px);' : 'height: calc(100vh - 272px);' }}">
            <div class="card-body table-responsive p-0">
                <div id="chart_div"></div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="{{ asset('material-pro/assets/plugins/select2/dist/js/select2.full.min.js') }}" type="text/javascript">
    </script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', {
            packages: ["orgchart"]
        });
        google.charts.setOnLoadCallback(drawChart);

        function drawChart(user_id = '{{ auth()->id() }}') {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Name');
            data.addColumn('string', 'Manager');
            data.addColumn('string', 'ToolTip');
            data.addRows(loadTree(user_id, true));
            var chart = new google.visualization.OrgChart(document.getElementById('chart_div'));
            chart.draw(data, {
                'allowHtml': true,
            });
        }

        function loadTree(id, init = false) {
            var jsonData = $.ajax({
                url: "{{ url('api/monoleg') }}?id=" + id + '&init=' + init,
                dataType: "json",
                async: false
            }).responseText;
            return JSON.parse(jsonData);
        }
    </script>
    @if (auth()->user()->type == 'admin')
        <script>
            jQuery(document).ready(function() {
                $("#users").select2({
                    placeholder: "Cari member...",
                    allowClear: true,
                    ajax: {
                        url: '/filter-user',
                        data: function(params) {
                            var query = {
                                search: params.term,
                                page: params.page || 1
                            }
                            // Query parameters will be ?search=[term]&page=[page]
                            return query;
                        },
                        processResults: function(data) {
                            return {
                                results: data.data,
                                pagination: {
                                    more: (data.current_page * data.per_page) < data.total
                                }
                            };
                        },
                        cache: true,
                    }
                }).on("select2:select", function(e) {
                    drawChart(e.params.data.id, true);
                });
            });
        </script>
    @endif
@endsection
