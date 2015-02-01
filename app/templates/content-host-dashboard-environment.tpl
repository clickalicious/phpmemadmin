<div class="col-md-4">
    <h2>Facts</h2>
    <ul class="list-group">
        <li class="list-group-item" style="height: {{latestVersionHeight}}px;">
            <span class="badge">{{version}}</span> Memcached Version {{latestVersion}}
        </li>
        <li class="list-group-item">
            <span class="badge">{{pid}}</span> Process ID (pid)
        </li>
        <li class="list-group-item">
            <span class="badge">{{starttime}}</span> Starttime
        </li>
        <li class="list-group-item">
            <span class="badge time">{{time}}</span> Time on server
        </li>
        <li class="list-group-item">
            <span class="badge timer">{{uptime}}</span> Uptime
        </li>
        <li class="list-group-item">
            <span class="badge">{{limit_maxmbytes}} MB</span> Total memory
        </li>
        <li class="list-group-item">
            <span class="badge">{{curr_connections}}</span> Active connections
        </li>
        <li class="list-group-item">
            <span class="badge">{{total_connections}}</span> Connections since start
        </li>
    </ul>
</div>
