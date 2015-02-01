<canvas id="{{memoryId}}" width="340" height="300"></canvas>

<script>
    var data = [
        {
            value: {{memoryMax}},
            color: "rgba(71, 178, 36, 1)",
            highlight: "rgba(71, 178, 36, 0.75)",
            label: "Memory available in bytes"
        },
        {
            value: {{memoryUsed}},
            color: "rgba(212, 63, 58, 1)",
            highlight: "rgba(212, 63, 58, 0.75)",
            label: "Memory used in bytes"
        }
    ];

    var ctx = document.getElementById('{{memoryId}}').getContext('2d');
    var {{memoryId}} = new Chart(ctx).Doughnut(data, {
        responsive: true,
        maintainAspectRatio: true
    });
</script>
