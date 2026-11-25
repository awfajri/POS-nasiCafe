import Chart from "chart.js/auto";

/**
 * Komponen Alpine untuk grafik penjualan Dashboard Owner.
 *
 * Data (harian/bulanan) disuntikkan lewat window.ownerDashboard oleh Blade
 * (json_encode di server, bukan dari input pengguna) sehingga aman dari XSS.
 */
window.ownerDashboardApp = function ownerDashboardApp() {
    return {
        chartPeriod: "daily",
        chartInstance: null,
        formatId: new Intl.NumberFormat("id-ID"),

        init() {
            this.$nextTick(() => this.renderChart());
            window.addEventListener("resize", () => this.chartInstance?.resize());
        },

        get activeData() {
            return this.chartPeriod === "daily"
                ? window.ownerDashboard.daily
                : window.ownerDashboard.monthly;
        },

        get activeLabel() {
            return this.chartPeriod === "daily" ? "Omzet Harian" : "Omzet Bulanan";
        },

        renderChart() {
            const canvas = this.$root.querySelector("#salesChart");
            if (!canvas) return;

            // Hancurkan instance lama agar tidak menumpuk saat toggle.
            if (this.chartInstance) {
                this.chartInstance.destroy();
                this.chartInstance = null;
            }

            const ctx = canvas.getContext("2d");

            const gradient = ctx.createLinearGradient(0, 0, 0, 320);
            gradient.addColorStop(0, "rgba(99, 102, 241, 0.35)");
            gradient.addColorStop(1, "rgba(99, 102, 241, 0.02)");

            this.chartInstance = new Chart(ctx, {
                type: "bar",
                data: {
                    labels: this.activeData.map((p) => p.label),
                    datasets: [
                        {
                            label: this.activeLabel,
                            data: this.activeData.map((p) => p.omzet),
                            backgroundColor: gradient,
                            borderColor: "#6366f1",
                            borderWidth: 1.5,
                            borderRadius: 6,
                            maxBarThickness: 44,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 400 },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: "#0f172a",
                            padding: 10,
                            cornerRadius: 10,
                            titleColor: "#e2e8f0",
                            bodyColor: "#ffffff",
                            callbacks: {
                                label: (ctx) =>
                                    ` ${ctx.dataset.label}: Rp ${this.formatId.format(ctx.parsed.y)}`,
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: "#64748b", font: { size: 11 } },
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: "#f1f5f9" },
                            border: { display: false },
                            ticks: {
                                color: "#64748b",
                                font: { size: 11 },
                                callback: (value) =>
                                    value >= 1_000_000
                                        ? `${(value / 1_000_000).toFixed(1)}jt`
                                        : value >= 1_000
                                            ? `${Math.round(value / 1_000)}rb`
                                            : value,
                            },
                        },
                    },
                },
            });
        },

        refreshChart() {
            this.renderChart();
        },
    };
};
