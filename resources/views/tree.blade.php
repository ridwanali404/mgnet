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
        
        /* Styling warna text untuk paket Gold */
        .package-gold.orgchart-node .title,
        .orgchart-node.package-gold .title {
            color: #FFD700 !important;
            font-weight: bold;
        }
        
        /* Styling warna text untuk paket Platinum */
        .package-platinum.orgchart-node .title,
        .orgchart-node.package-platinum .title {
            color: #C0C0C0 !important;
            font-weight: bold;
        }
        
        /* Styling warna text untuk paket Free */
        .package-free.orgchart-node .title,
        .orgchart-node.package-free .title {
            color: #808080 !important;
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
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="btn-group" role="group" aria-label="Tree Type">
                    <button type="button" class="btn btn-primary active" id="treeTypeUpline" data-tree-type="upline">
                        Tree Upline
                    </button>
                    <button type="button" class="btn btn-outline-primary" id="treeTypeSponsor" data-tree-type="sponsor">
                        Tree Sponsor
                    </button>
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

    <!-- Modal Recent Bonus -->
    <div class="modal fade" id="recentBonusModal" tabindex="-1" role="dialog" aria-labelledby="recentBonusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="recentBonusModalLabel">Recent Bonus - <span id="modalUserName"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="bonusLoading" class="text-center" style="display: none;">
                        <div class="spinner-border" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p>Memuat data bonus...</p>
                    </div>
                    <div id="bonusContent" style="display: none;">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>Tipe</th>
                                        <th>Deskripsi</th>
                                        <th>Jumlah</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="bonusTableBody">
                                </tbody>
                            </table>
                        </div>
                        <div id="bonusEmpty" style="display: none;" class="text-center text-muted">
                            <p>Tidak ada data bonus</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
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
            
            // Handle tree type toggle
            $('#treeTypeUpline, #treeTypeSponsor').on('click', function() {
                var treeType = $(this).data('tree-type');
                currentTreeType = treeType;
                
                // Update button states
                $('#treeTypeUpline, #treeTypeSponsor').removeClass('active btn-primary').addClass('btn-outline-primary');
                $(this).removeClass('btn-outline-primary').addClass('active btn-primary');
                
                // Reload tree
                var selectedUserId = $('#users').val();
                initOrgChart(selectedUserId || null);
            });
        });

        var currentTreeType = 'upline'; // default tree type
        
        function initOrgChart(user_id = null) {
            $('#chart-container').html('');
            $.ajax({
                dataType: "json",
                url: '/tree/dataSource',
                data: {
                    user_id: user_id,
                    tree_type: currentTreeType
                },
                success: function(response) {
                    var ajaxURLs = {
                        'children': function(nodeData) {
                            return '/tree/children/' + nodeData.id + '?tree_type=' + currentTreeType;
                        },
                        'parent': function(nodeData) {
                            return '/tree/parent/' + nodeData.id + '?tree_type=' + currentTreeType;
                        },
                        'siblings': function(nodeData) {
                            return '/tree/siblings/' + nodeData.id + '?tree_type=' + currentTreeType;
                        },
                        'families': function(nodeData) {
                            return '/tree/families/' + nodeData.id + '?tree_type=' + currentTreeType;
                        }
                    };
                    
                    // Store package data untuk digunakan nanti
                    var packageDataMap = {};
                    function buildPackageMap(nodeData) {
                        if (nodeData) {
                            packageDataMap[nodeData.id] = nodeData.packageClass || '';
                            if (nodeData.children) {
                                nodeData.children.forEach(function(child) {
                                    buildPackageMap(child);
                                });
                            }
                        }
                    }
                    buildPackageMap(response);
                    
                    // Function untuk apply package class
                    function applyPackageClassToNode(nodeId, packageClass) {
                        if (packageClass) {
                            // Coba beberapa selector yang mungkin digunakan orgchart
                            var selectors = [
                                '[data-id="' + nodeId + '"]',
                                '.orgchart-node[data-id="' + nodeId + '"]',
                                '#chart-container [data-id="' + nodeId + '"]'
                            ];
                            
                            selectors.forEach(function(selector) {
                                var $node = $(selector);
                                if ($node.length) {
                                    $node.addClass(packageClass);
                                    // Juga coba find parent orgchart-node
                                    $node.closest('.orgchart-node').addClass(packageClass);
                                }
                            });
                            
                            // Coba dengan mencari berdasarkan text content
                            $('#chart-container').find('.orgchart-node').each(function() {
                                var $node = $(this);
                                var nodeText = $node.text();
                                // Cek apakah node ini sesuai dengan data
                                if (response.id == nodeId || (response.children && response.children.some(function(c) {
                                    return c.id == nodeId && nodeText.indexOf(c.name) !== -1;
                                }))) {
                                    $node.addClass(packageClass);
                                }
                            });
                        }
                    }
                    
                    var orgchart = $('#chart-container').orgchart({
                        'data': response,
                        'ajaxURL': ajaxURLs,
                        'nodeContent': 'title',
                        'nodeId': 'id',
                        'createNode': function($node, data) {
                            // Apply package class langsung saat node dibuat
                            if (data.packageClass) {
                                $node.addClass(data.packageClass);
                                
                                var $title = $node.find('.title');
                                
                                // Hanya ubah warna text di title sesuai package
                                if (data.packageClass === 'package-gold') {
                                    $title.css({
                                        'color': '#FFD700',
                                        'font-weight': 'bold'
                                    });
                                } else if (data.packageClass === 'package-platinum') {
                                    $title.css({
                                        'color': '#C0C0C0',
                                        'font-weight': 'bold'
                                    });
                                } else if (data.packageClass === 'package-free') {
                                    $title.css({
                                        'color': '#808080'
                                    });
                                }
                            }
                            
                            // Tambahkan event handler untuk klik node (jika ada function showRecentBonuses)
                            if (typeof showRecentBonuses === 'function') {
                                $node.on('click', function(e) {
                                    e.stopPropagation();
                                    var userId = data.id;
                                    showRecentBonuses(userId, data.name || data.title);
                                });
                                
                                // Tambahkan cursor pointer untuk menunjukkan bahwa node bisa diklik
                                $node.css('cursor', 'pointer');
                            }
                        }
                    });
                    
                    // Function untuk apply package class/style
                    function applyPackageStyle() {
                        // Iterate melalui semua node di orgchart
                        $('#chart-container').find('.orgchart-node').each(function() {
                            var $node = $(this);
                            var nodeId = $node.attr('data-id') || $node.closest('[data-id]').attr('data-id');
                            
                            if (!nodeId) {
                                // Coba cari berdasarkan text content
                                var nodeText = $node.text().trim();
                                Object.keys(packageDataMap).forEach(function(id) {
                                    var nodeData = findNodeInData(response, id);
                                    if (nodeData && (nodeText.indexOf(nodeData.name) !== -1 || nodeText.indexOf(nodeData.title) !== -1)) {
                                        nodeId = id;
                                    }
                                });
                            }
                            
                            if (nodeId && packageDataMap[nodeId]) {
                                var packageClass = packageDataMap[nodeId];
                                $node.addClass(packageClass);
                                
                                // Apply inline style untuk warna text di title
                                var $title = $node.find('.title');
                                
                                if (packageClass === 'package-gold') {
                                    $title.css('color', '#FFD700').css('font-weight', 'bold');
                                } else if (packageClass === 'package-platinum') {
                                    $title.css('color', '#C0C0C0').css('font-weight', 'bold');
                                } else if (packageClass === 'package-free') {
                                    $title.css('color', '#808080');
                                }
                            }
                        });
                    }
                    
                    // Helper function untuk find node dalam data
                    function findNodeInData(data, nodeId) {
                        if (String(data.id) === String(nodeId)) {
                            return data;
                        }
                        if (data.children) {
                            for (var i = 0; i < data.children.length; i++) {
                                var found = findNodeInData(data.children[i], nodeId);
                                if (found) return found;
                            }
                        }
                        return null;
                    }
                    
                    // Apply style setelah orgchart di-render dengan multiple attempts
                    var attempts = [300, 600, 1000, 2000];
                    attempts.forEach(function(delay) {
                        setTimeout(applyPackageStyle, delay);
                    });
                    
                    // Gunakan MutationObserver untuk detect node baru
                    var observer = new MutationObserver(function(mutations) {
                        applyPackageStyle();
                    });
                    
                    observer.observe(document.getElementById('chart-container'), {
                        childList: true,
                        subtree: true
                    });
                    
                    // Handle children yang di-load via ajax
                    $(document).ajaxSuccess(function(event, xhr, settings) {
                        if (settings.url && (settings.url.includes('/tree/children/') || 
                            settings.url.includes('/tree/parent/') || 
                            settings.url.includes('/tree/siblings/') || 
                            settings.url.includes('/tree/families/'))) {
                            setTimeout(function() {
                                try {
                                    var ajaxResponse = JSON.parse(xhr.responseText);
                                    if (ajaxResponse.children) {
                                        ajaxResponse.children.forEach(function(child) {
                                            if (child.id && child.packageClass) {
                                                packageDataMap[child.id] = child.packageClass;
                                            }
                                        });
                                    }
                                    if (ajaxResponse.id && ajaxResponse.packageClass) {
                                        packageDataMap[ajaxResponse.id] = ajaxResponse.packageClass;
                                    }
                                    applyPackageStyle();
                                    
                                    // Tambahkan event handler untuk node baru yang di-load via AJAX
                                    $('#chart-container').find('.orgchart-node').off('click.bonus').on('click.bonus', function(e) {
                                        e.stopPropagation();
                                        var $node = $(this);
                                        var nodeId = $node.attr('data-id') || $node.closest('[data-id]').attr('data-id');
                                        
                                        if (!nodeId) {
                                            // Coba cari dari text content
                                            var nodeText = $node.find('.title').text() || $node.text();
                                            // Cari di response data
                                            function findNodeIdInData(data, searchText) {
                                                if (data.id && (data.name === searchText || data.title === searchText)) {
                                                    return data.id;
                                                }
                                                if (data.children) {
                                                    for (var i = 0; i < data.children.length; i++) {
                                                        var found = findNodeIdInData(data.children[i], searchText);
                                                        if (found) return found;
                                                    }
                                                }
                                                return null;
                                            }
                                            nodeId = findNodeIdInData(response, nodeText.trim());
                                        }
                                        
                                        if (nodeId) {
                                            var nodeName = $node.find('.title').text() || $node.text();
                                            showRecentBonuses(nodeId, nodeName);
                                        }
                                    });
                                } catch(e) {
                                    // Ignore parse errors
                                }
                            }, 200);
                        }
                    });
                    
                    // Tambahkan event handler untuk semua node yang sudah ada
                    setTimeout(function() {
                        $('#chart-container').find('.orgchart-node').off('click.bonus').on('click.bonus', function(e) {
                            e.stopPropagation();
                            var $node = $(this);
                            var nodeId = $node.attr('data-id') || $node.closest('[data-id]').attr('data-id');
                            
                            if (!nodeId) {
                                // Coba cari dari text content
                                var nodeText = $node.find('.title').text() || $node.text();
                                // Cari di response data
                                function findNodeIdInData(data, searchText) {
                                    if (data.id && (data.name === searchText || data.title === searchText)) {
                                        return data.id;
                                    }
                                    if (data.children) {
                                        for (var i = 0; i < data.children.length; i++) {
                                            var found = findNodeIdInData(data.children[i], searchText);
                                            if (found) return found;
                                        }
                                    }
                                    return null;
                                }
                                nodeId = findNodeIdInData(response, nodeText.trim());
                            }
                            
                            if (nodeId) {
                                var nodeName = $node.find('.title').text() || $node.text();
                                showRecentBonuses(nodeId, nodeName);
                            }
                        });
                    }, 500);
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
    
    <script>
        function showRecentBonuses(userId, userName) {
            // Tampilkan modal
            $('#recentBonusModal').modal('show');
            $('#modalUserName').text(userName || 'User #' + userId);
            
            // Reset content
            $('#bonusLoading').show();
            $('#bonusContent').hide();
            $('#bonusEmpty').hide();
            $('#bonusTableBody').empty();
            
            // Load data dengan AJAX
            $.ajax({
                url: '/tree/recent-bonuses/' + userId,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    $('#bonusLoading').hide();
                    
                    if (response.bonuses && response.bonuses.length > 0) {
                        $('#bonusContent').show();
                        var tbody = $('#bonusTableBody');
                        tbody.empty();
                        
                        response.bonuses.forEach(function(bonus, index) {
                            var status = '';
                            if (bonus.paid_at) {
                                status = '<span class="badge badge-success">Dibayar</span>';
                            } else if (bonus.used_at) {
                                status = '<span class="badge badge-info">Digunakan</span>';
                            } else {
                                status = '<span class="badge badge-warning">Belum Dibayar</span>';
                            }
                            
                            var amountText = bonus.is_poin ? 
                                bonus.amount.toLocaleString('id-ID') + ' Poin' : 
                                'Rp ' + bonus.amount.toLocaleString('id-ID');
                            
                            var row = '<tr>' +
                                '<td>' + (index + 1) + '</td>' +
                                '<td>' + bonus.created_at + '</td>' +
                                '<td>' + bonus.type + '</td>' +
                                '<td>' + (bonus.description || '-') + '</td>' +
                                '<td>' + amountText + '</td>' +
                                '<td>' + status + '</td>' +
                                '</tr>';
                            tbody.append(row);
                        });
                    } else {
                        $('#bonusEmpty').show();
                    }
                },
                error: function(xhr, status, error) {
                    $('#bonusLoading').hide();
                    $('#bonusEmpty').show();
                    $('#bonusEmpty').html('<p class="text-danger">Error memuat data bonus: ' + error + '</p>');
                }
            });
        }
    </script>
@endsection
