
    <!-- START: content -->
    <div class="row">
        <div class="col-md-12">
            <div class="page-header">
                <h1><span class="text-muted"><span class="glyphicon glyphicon-tags"></span> Data</span> {{hostFull}}<br><small>create, read, update or delete key/value pairs</small></h1>
            </div>
        </div>

        <!-- start data management console/table -->
        <div class="col-md-6">
            Currently the flags used by <i>Memcached.php</i> are not fully but mostly compatible with <i>Memcached</i> extension from <i>PECL</i> (Flags 0 - 4 are supported).
        </div>
        <div class="col-md-6">
            <div class="pull-right">
                <div class="btn-group">
                    <a class="btn btn-primary" href="javascript:void(0);" onclick="return setKey();"><span class="glyphicon glyphicon-plus"></span>&nbsp;Create new key/value pair</a>
                </div>
                <div class="btn-group">
                    <a class="btn btn-danger" href="javascript:void(0);" onclick="return flushKeys();"><span class="glyphicon glyphicon-trash"></span>&nbsp;Flush all keys!</a>
                </div>
            </div>
        </div>

        <!-- start data management console/table -->
        <div class="col-md-12">
            <div class="panel panel-default top20">
                <div class="panel-heading">
                    <h3 class="panel-title">Stored key/value pairs</h3>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table id="storedKeys" class="dt-responsive table table-bordered table-hover table-striped">
                            <thead>
                                <tr>
                                    <th class="one_t">Key</th>
                                    <th class="one_h">Value</th>
                                    <th>Bytes</th>
                                    <th>Type</th>
                                    <th>Flags</th>
                                    <th>CAS</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{content}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: content -->
