<template>
	<div class="chart-container" style="width: 1200px">
		<canvas ref="canvasRef"></canvas>
	</div>
	<hr />
	<div @keyup.enter="update">
		<Input v-for="(item, index) in items" :id="index" :data="item" @change="change" @remove="remove" />
	</div>
	<div class="row">
		<div class="col">
			<button class="btn btn-primary" :disable="rendered" @click="update">Update</button>
		</div>
		<div class="col">
			<button class="btn btn-primary" :disable="rendered" @click="add">Add new</button>
		</div>
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
import { defaultItems } from './items.js'
import Input from './Input.vue'
Chart.register(...registerables)

const BONE_AT = 1973
const items = ref(defaultItems)
const graphData = ref({
	labels: [],
	datasets: [],
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
				text: '年/月額(万)',
			},
			ticks: {
				callback: function (value, index, ticks) {
					return `${value} / ${Math.round(value / 12)}`
				},
			},
		},
	},
}
const canvasRef = ref(null)
const rendered = ref(false)

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
const randomColor = () => Math.floor(Math.random() * 16777215).toString(16)
const initGraph = () => {
	graphData.value.labels.splice(0)
	graphData.value.datasets.splice(0)
	items.value.forEach((i) => {
		graphData.value.datasets.push({
			label: 'データ',
			data: [],
			fill: false,
			borderColor: '#' + randomColor(),
			tension: 0.1,
			yAxisID: 'y',
		})
	})
}
onMounted(() => {
	initGraph()
	init()
})
const remove = (e) => {
	const index = items.value.findIndex((item) => item.name === e.name)
	if (index !== -1) {
		items.value.splice(index, 1)
		graphData.value.datasets.splice(index, 1)
		graphData.value.labels.splice(index, 1)
	}
}
const add = () => {
	items.value.push({
		name: 'new',
		rate1: 10,
		rate2: 8,
		start_year: 2024,
		asset_start: 0,
		pay_per_month: 5,
		withdraw: 17,
		year_change_rate: 62,
		end_age: 60,
	})
	graphData.value.datasets.push({})
	graphData.value.labels.push({
		label: 'データ',
		data: [],
		fill: false,
		borderColor: '#674373',
		tension: 0.1,
		yAxisID: 'y',
	})
}
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
			initGraph()
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
