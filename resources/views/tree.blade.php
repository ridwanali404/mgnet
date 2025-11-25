@extends('layout.app')
@section('title', 'Tree')
@section('style')
    <link href="{{ asset('material-pro/assets/plugins/select2/dist/css/select2.min.css') }}" rel="stylesheet"
        type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/orgchart/3.8.0/css/jquery.orgchart.min.css" />
    <style>
        #chart-container {
            position: relative;
            height: 100%;
            overflow: auto;
            text-align: center;
        }
    </style>
@endsection
@php
@endphp
@section('content')
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-5 col-8 align-self-center">
                <h3 class="text-themecolor m-b-0 m-t-0">Tree</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="javascript:void(0)">Home</a>
                    </li>
                    <li class="breadcrumb-item active">Tree</li>
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
                <div id="chart-container"></div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="{{ asset('material-pro/assets/plugins/select2/dist/js/select2.full.min.js') }}" type="text/javascript">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/orgchart/3.8.0/js/jquery.orgchart.min.js"></script>
    <script type="text/javascript" src="https://dabeng.github.io/OrgChart/js/jquery.mockjax.min.js"></script>
    <script type="text/javascript">
        $(function() {

            $.mockjax({
                url: '/orgchart/children/n3',
                contentType: 'application/json',
                responseTime: 1000,
                responseText: {
                    'children': [{
                            'id': 'n4',
                            'name': 'Pang Pang',
                            'title': 'engineer',
                            'relationship': '110'
                        },
                        {
                            'id': 'n5',
                            'name': 'Xiang Xiang',
                            'title': 'UE engineer',
                            'relationship': '110'
                        }
                    ]
                }
            });

            $.mockjax({
                url: '/orgchart/parent/n1',
                contentType: 'application/json',
                responseTime: 1000,
                responseText: {
                    'id': 'n6',
                    'name': 'Lao Lao',
                    'title': 'general manager',
                    'relationship': '001'
                }
            });

            $.mockjax({
                url: '/orgchart/siblings/n1',
                contentType: 'application/json',
                responseTime: 1000,
                responseText: {
                    'siblings': [{
                            'id': '7',
                            'name': 'Bo Miao',
                            'title': 'department engineer',
                            'relationship': '110'
                        },
                        {
                            'id': '8',
                            'name': 'Yu Jie',
                            'title': 'department engineer',
                            'relationship': '110'
                        },
                        {
                            'id': '9',
                            'name': 'Yu Li',
                            'title': 'department engineer',
                            'relationship': '110'
                        },
                        {
                            'id': '10',
                            'name': 'Hong Miao',
                            'title': 'department engineer',
                            'relationship': '110'
                        },
                        {
                            'id': '11',
                            'name': 'Yu Wei',
                            'title': 'department engineer',
                            'relationship': '110'
                        },
                        {
                            'id': '12',
                            'name': 'Chun Miao',
                            'title': 'department engineer',
                            'relationship': '110'
                        },
                        {
                            'id': '13',
                            'name': 'Yu Tie',
                            'title': 'department engineer',
                            'relationship': '110'
                        }
                    ]
                }
            });

            $.mockjax({
                url: '/orgchart/families/n1',
                contentType: 'application/json',
                responseTime: 1000,
                responseText: {
                    'id': 'n6',
                    'name': 'Lao Lao',
                    'title': 'general manager',
                    'relationship': '001',
                    'children': [{
                            'id': 'n7',
                            'name': 'Bo Miao',
                            'title': 'department engineer',
                            'relationship': '110'
                        },
                        {
                            'id': 'n8',
                            'name': 'Yu Jie',
                            'title': 'department engineer',
                            'relationship': '110'
                        },
                        {
                            'id': 'n9',
                            'name': 'Yu Li',
                            'title': 'department engineer',
                            'relationship': '110'
                        },
                        {
                            'id': 'n10',
                            'name': 'Hong Miao',
                            'title': 'department engineer',
                            'relationship': '110'
                        },
                        {
                            'id': 'n11',
                            'name': 'Yu Wei',
                            'title': 'department engineer',
                            'relationship': '110'
                        },
                        {
                            'id': 'n12',
                            'name': 'Chun Miao',
                            'title': 'department engineer',
                            'relationship': '110'
                        },
                        {
                            'id': 'n13',
                            'name': 'Yu Tie',
                            'title': 'department engineer',
                            'relationship': '110'
                        }
                    ]
                }
            });
            initOrgChart();
        });

        function initOrgChart(user_id = null) {
            $('#chart-container').html('');
            $.ajax({
                dataType: "json",
                url: '/tree/dataSource',
                data: {
                    user_id: user_id
                },
                success: function(response) {
                    var ajaxURLs = {
                        'children': '/tree/children/',
                        'parent': '/tree/parent/',
                        'siblings': function(nodeData) {
                            return '/tree/siblings/' + nodeData.id;
                        },
                        'families': function(nodeData) {
                            return '/tree/families/' + nodeData.id;
                        }
                    };
                    $('#chart-container').orgchart({
                        'data': response,
                        'ajaxURL': ajaxURLs,
                        'nodeContent': 'title',
                        'nodeId': 'id'
                    });
                },
            });
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
                    initOrgChart(e.params.data.id);
                });
            });
        </script>
    @endif
@endsection
