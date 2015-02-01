<div class="col-md-4">
    <h2>Memory</h2>

    <!-- full width but height important -->
    <canvas id="chart-memory" height="300"></canvas>

    <script>
        // Json input for chart
        var data = [
            {
                value: {{limit_maxbytes}},
                color: "rgba(71, 178, 36, 1)",
                highlight: "rgba(71, 178, 36, 0.75)",
                label: "Memory available in bytes"
            },
            {
                value: {{bytes}},
                color: "rgba(212, 63, 58, 1)",
                highlight: "rgba(212, 63, 58, 0.75)",
                label: "Memory used in bytes"
            }
        ];

        // Draw chart
        new Chart(
            document.getElementById('chart-memory').getContext('2d')
        ).Doughnut(
            data,
            {
                responsive: true,
                maintainAspectRatio: false,
                segmentShowStroke : true,
                legendTemplate : "Hallo"
            }
        );
    </script>
</div>
