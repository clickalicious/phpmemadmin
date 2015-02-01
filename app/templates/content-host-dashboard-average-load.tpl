
            <div class="col-md-12" id="loadContainer">
                <h2>Average Load</h2>

                <!-- full width but height important -->
                <canvas id="chart-load" height="330"></canvas>

                <script>
                    // Json input for chart
                    var data = {
                        labels: ["Requests", "Hit", "Miss", "Set"],
                        datasets: [
                            {
                                label: "Requests/s",
                                fillColor: "rgba(102, 102, 102, 0.25)",
                                strokeColor: "rgba(102, 102, 102, 1)",
                                pointColor: "rgba(102, 102, 102, 0.75)",
                                pointStrokeColor: "rgba(102, 102, 102, 1)",
                                pointHighlightFill: "#fff",
                                pointHighlightStroke: "rgba(102, 102, 102, 1)",
                                data: {{seconds}}
                            },
                            {
                                label: "Requests/min",
                                fillColor: "rgba(153, 153, 153, 0.25)",
                                strokeColor: "rgba(153, 153, 153, 1)",
                                pointColor: "rgba(153, 153, 153, 0.75)",
                                pointStrokeColor: "rgba(153, 153, 153, 1)",
                                pointHighlightFill: "#fff",
                                pointHighlightStroke: "rgba(153, 153, 153, 1)",
                                data: {{minutes}}
                            },
                            {
                                label: "Requests/h",
                                fillColor: "rgba(204, 204, 204, 0.25)",
                                strokeColor: "rgba(204, 204, 204, 1)",
                                pointColor: "rgba(204, 204, 204, 0.75)",
                                pointStrokeColor: "rgba(204, 204, 204, 1)",
                                pointHighlightFill: "#fff",
                                pointHighlightStroke: "rgba(204, 204, 204, 1)",
                                data: {{hours}}
                            },
                            {
                                label: "Requests/d",
                                fillColor: "rgba(170, 170, 170, 0.25)",
                                strokeColor: "rgba(170, 170, 170, 1)",
                                pointColor: "rgba(170, 170, 170, 0.75)",
                                pointStrokeColor: "rgba(170, 170, 170, 1)",
                                pointHighlightFill: "#fff",
                                pointHighlightStroke: "rgba(170, 170, 170, 1)",
                                data: {{days}}
                            }
                        ]
                    };

                    // Draw chart
                    new Chart(
                        document.getElementById('chart-load').getContext('2d')
                    ).Line(
                        data,
                        {
                            responsive: true,
                            maintainAspectRatio: false
                        }
                    );
                </script>
            </div>
