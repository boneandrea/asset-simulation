<template>
	<div class="chart-container" style="width: 1200px">
		<canvas ref="canvasRef"></canvas>
	</div>
	<hr />
	<div @keyup.enter="update">
		<Input v-for="(item, index) in items" :id="index" :data="item" @change="change" />
	</div>
	<div class="input-group mb-3">
		<button class="btn btn-primary" :disable="rendered" @click="update">Update</button>
	</div>
</template>
<script setup>
defineProps({
	msg: {
		type: String,
		required: true,
	},
})
import { ref, onMounted } from 'vue'
import { Chart, registerables } from 'chart.js'
import Input from './Input.vue'
Chart.register(...registerables)

const BONE_AT = 1973
const items = ref([
	{
		name: 'sony',
		rate1: 10,
		rate2: 8,
		start_year: 2007,
		asset_start: 0,
		pay_per_month: 1.55,
		withdraw: 17,
		year_change_rate: 62,
		end_age: 60,
	},
	{
		name: 'rakuten',
		rate1: 11,
		rate2: 8,
		start_year: 2024,
		asset_start: 2900,
		pay_per_month: 20,
		withdraw: 62,
		year_change_rate: 62,
		end_age: 57,
	},
	{
		name: 'india',
		rate1: 30,
		rate2: 8,
		asset_start: 50,
		start_year: 2024,
		pay_per_month: 10,
		withdraw: 39,
		year_change_rate: 62,
		end_age: 57,
	},
])
const change = (e, a, b) => {
	console.log(e, a)
}
const canvasRef = ref(null)
const rendered = ref(false)
const graphData = ref({
	labels: [],
	datasets: [
		{
			label: 'データ',
			data: [],
			fill: false,
			borderColor: 'rgb(75, 192, 192)',
			tension: 0.1,
			yAxisID: 'y',
		},
		{
			label: 'データ',
			data: [],
			fill: false,
			borderColor: '#a23456',
			tension: 0.1,
			yAxisID: 'y',
		},
		{
			label: 'データ',
			data: [],
			fill: false,
			borderColor: '#674373',
			tension: 0.1,
			yAxisID: 'y',
		},
	],
})
const options = {
	responsive: true,
	scales: {
		y: {
			type: 'linear',
			display: true,
			position: 'left',
			title: {
				display: true,
				text: '資産総額(万)',
			},
		},
		y1: {
			type: 'linear',
			display: true,
			position: 'right',
			title: {
				display: true,
				text: '年額(万)',
			},
			ticks: {
				//callback: function(value, index, ticks) {
				//return `${value}万`
				//}
			},
		},
	},
}

const init = () => {
	if (canvasRef.value === null) return
	const canvas = canvasRef.value.getContext('2d')
	if (canvas === null) return
	rendered.value = false
	chart.value = new Chart(canvas, {
		type: 'line',
		data: graphData.value,
		options,
	})
	rendered.value = true
}
const chart = ref(null)
onMounted(() => {
	init()
})
const update = () => {
	chart.value.destroy()
	const api = 'http://localhost:8888/cal.php'
	fetch(api, {
		method: 'POST',
		headers: { 'content-type': 'application/json' },
		body: JSON.stringify({
			data: items.value,
		}),
	})
		.then((response) => {
			return response.json()
		})
		.then((r) => {
			if (r.status === 'error') {
				throw new Error(r['reason'])
			}

			// set labels
			const xaxis = {}
			console.clear()
			graphData.value.labels.splice(0)
			graphData.value.datasets.forEach((_d, i) => graphData.value.datasets[i].data.splice(0))

			const YEAR = 2024
			const collectXaxis = {}
			r.forEach((data, index) => {
				data.data.forEach((e) => (collectXaxis[e.year] = true))
			})

			const labels = Object.keys(collectXaxis)
				.map((e) => parseInt(e))
				.sort()
				.filter((year) => year >= YEAR)
			labels.forEach((d) => graphData.value.labels.push(`${d}/${d - BONE_AT}`))
			const num_items = r.length
			const SUM_GRAPH_INDEX = r.length
			const SPENT_GRAPH_INDEX = r.length + 1
			graphData.value.datasets.push(
				{
					label: 'TOTAL',
					data: [],
					fill: false,
					borderColor: '#E74C3C',
					tension: 0.1,
					yAxisID: 'y',
				},
				{
					label: '浪費',
					data: [],
					fill: false,
					borderColor: '#11bC3C',
					tension: 0.1,
					yAxisID: 'y1', // 追加
				}
			)

			r.forEach((data, index) => {
				console.log(data)

				graphData.value.datasets[index].label = data.name
				data.data
					.filter((e) => e.year >= YEAR)
					.forEach((e, i) => {
						// 自グラフ
						graphData.value.datasets[index].data[labels.indexOf(e.year)] = e.asset
						// total
						graphData.value.datasets[SUM_GRAPH_INDEX].data[i] =
							(graphData.value.datasets[SUM_GRAPH_INDEX].data[i] ?? 0) + e.asset

						// 消費
						if (e.year >= BONE_AT + 57) {
							console.log(Array.from(items.value))
							const sum = e.asset > items.value[index].withdraw * 12 ? items.value[index].withdraw * 12 : e.asset
							graphData.value.datasets[SPENT_GRAPH_INDEX].data[i] =
								(graphData.value.datasets[SPENT_GRAPH_INDEX].data[i] ?? 0) + sum
						}
					})
			})

			init()
		})
		.catch((e) => {
			console.error(e)
			alert(e)
		})
		.finally(() => {})
}
</script>
<style scoped>
canvas {
	width: 100%;
}
</style>
