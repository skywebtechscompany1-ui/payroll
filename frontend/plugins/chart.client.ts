import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  RadialLinearScale
} from 'chart.js'
import {
  LineChart,
  BarChart,
  DoughnutChart,
  PieChart,
  PolarAreaChart,
  RadarChart
} from 'vue-chartjs'

// Register Chart.js components
ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  RadialLinearScale
)

export default defineNuxtPlugin((nuxtApp) => {
  nuxtApp.vueApp.component('LineChart', LineChart)
  nuxtApp.vueApp.component('BarChart', BarChart)
  nuxtApp.vueApp.component('DoughnutChart', DoughnutChart)
  nuxtApp.vueApp.component('PieChart', PieChart)
  nuxtApp.vueApp.component('PolarAreaChart', PolarAreaChart)
  nuxtApp.vueApp.component('RadarChart', RadarChart)

  return {
    provide: {
      ChartJS
    }
  }
})