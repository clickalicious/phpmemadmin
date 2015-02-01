<div class="col-md-4">
    <h2>Stored Keys</h2>

    <!-- full width but height important -->
    <canvas id="chart-items" height="300"></canvas>

    <script>
        // Json input for chart
        var data = {
            labels: ["Active", "Total since start"],
            datasets: [
                {
                    label: "Items",
                    fillColor: "rgba(51, 122, 183, 1)",
                    strokeColor: "rgba(51, 122, 183, 1)",
                    highlightFill: "rgba(51, 122, 183, 0.75)",
                    highlightStroke: "rgba(51, 122, 183, 1)",
                    data: [{{curr_items}}, {{total_items}}]
                }
            ]
        };

        // Draw chart
        new Chart(
            document.getElementById('chart-items').getContext('2d')
        ).Bar(
            data,
            {
                responsive: true,
                maintainAspectRatio: false
            }
        );
    </script>
</div>
