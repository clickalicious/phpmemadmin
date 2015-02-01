
        <div class="col-md-12">
            <h2>Requests</h2>

            <!-- full width but height important -->
            <canvas id="chart-request" height="300"></canvas>

            <script>
                // Json input for chart
                var data = {
                    labels: ["Get", "Delete", "Increment", "Decrement", "CAS"],
                    datasets: [
                        {
                            label: "Hits",
                            fillColor: "rgba(71, 178, 36, 1)",
                            strokeColor: "rgba(71, 178, 36, 1)",
                            highlightFill: "rgba(71, 178, 36, 0.75)",
                            highlightStroke: "rgba(71, 178, 36, 1)",
                            data: {{requestHits}}
                        },
                        {
                            label: "Misses",
                            fillColor: "rgba(212, 63, 58, 1)",
                            strokeColor: "rgba(212, 63, 58, 1)",
                            highlightFill: "rgba(212, 63, 58, 0.75)",
                            highlightStroke: "rgba(212, 63, 58, 1)",
                            data: {{requestMisses}}
                        }
                    ]
                };

                // Draw chart
                new Chart(
                    document.getElementById('chart-request').getContext('2d')
                ).Bar(
                    data,
                    {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                );
            </script>
        </div>
