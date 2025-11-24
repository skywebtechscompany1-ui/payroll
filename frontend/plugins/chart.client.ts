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
import { Line, Bar, Doughnut, Pie, PolarArea, Radar } from 'vue-chartjs'

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
  // Register chart components using vue-chartjs v5 approach
  nuxtApp.vueApp.component('LineChart', Line)
  nuxtApp.vueApp.component('BarChart', Bar)
  nuxtApp.vueApp.component('DoughnutChart', Doughnut)
  nuxtApp.vueApp.component('PieChart', Pie)
  nuxtApp.vueApp.component('PolarAreaChart', PolarArea)
  nuxtApp.vueApp.component('RadarChart', Radar)

  return {
    provide: {
      ChartJS
    }
  }
})