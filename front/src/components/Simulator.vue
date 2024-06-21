<template>
	<div class="chart-container" style="width: 1200px">
		<canvas ref="canvasRef"></canvas>
	</div>
	<hr />
	<div class="items">
		<div @keyup.enter="update">
			<Input v-for="(item, index) in items" :id="index" :data="item" @change="change" @remove="remove" />
		</div>
		<div class="row">
			<div class="col">
				<button class="w-25 btn btn-primary" :disable="rendered" @click="add">Add new</button>
			</div>
			<div class="col">
				<button class="btn btn-primary w-25" :disable="rendered" @click="update">Update</button>
			</div>
		</div>
		<hr />
		<div class="row md-1">
			<div class="col-mr-3">
				<button class="btn mr-3 btn-info" @click="save">Save</button>
				<button class="btn mr-3 btn-info" @click="restore">Restore</button>
			</div>
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
const API_ROOT = import.meta.env.VITE_API_ROOT

// データ
const items = ref(defaultItems)
const graphData = ref({
	labels: [],
	datasets: [],
})

// グラフ設定
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
				text: '年/月額(万) : 税引前',
			},
			ticks: {
				callback: function (value, index, ticks) {
					return `${value} / ${Math.round(value / 12)}`
				},
			},
		},
	},
}
const randomColor = () => '#' + Math.floor(Math.random() * 16777215).toString(16)
const chart = ref(null)
const canvasRef = ref(null)
const rendered = ref(false)

// ページ初期化
const renderGraph = () => {
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

// グラフ設定
const configureGraph = () => {
	graphData.value.labels.splice(0)
	graphData.value.datasets.splice(0)
	items.value.forEach((i) => {
		graphData.value.datasets.push({
			label: i.name,
			data: [],
			fill: false,
			borderColor: randomColor(),
			tension: 0.1,
			yAxisID: 'y',
		})
	})
}
onMounted(() => {
	renderGraph()
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
		name: null,
		rate1: null,
		rate2: null,
		start_year: null,
		asset_start: null,
		pay_per_month: null,
		withdraw: null,
		year_change_rate: null,
		end_age: null,
	})
	graphData.value.datasets.push({})
	graphData.value.labels.push({
		label: 'データ',
		data: [],
		fill: false,
		borderColor: randomColor(),
		tension: 1,
		yAxisID: 'y',
	})
}
const update = () => {
	chart.value.destroy()
	const api = `${API_ROOT}/cal.php`
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
			configureGraph()

			const CURRENT_YEAR = new Date().getFullYear()
			const collectXaxis = {}
			r.forEach((data, index) => {
				data.data.forEach((e) => (collectXaxis[e.year] = true))
			})

			const labels = Object.keys(collectXaxis)
				.map((e) => parseInt(e))
				.sort()
				.filter((year) => year >= CURRENT_YEAR)
			labels.forEach((d) => graphData.value.labels.push(`${d}/${d - BONE_AT}`))

			const SUM_GRAPH_INDEX = r.length
			const SPENT_GRAPH_INDEX = r.length + 1
			graphData.value.datasets.push(
				{
					label: 'TOTAL',
					data: [],
					fill: false,
					borderWidth: 4,
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
					.filter((e) => e.year >= CURRENT_YEAR)
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

			renderGraph()
		})
		.catch((e) => {
			console.error(e)
			alert(e)
		})
		.finally(() => {})
}
const save = () => {
	localStorage.setItem('assets', JSON.stringify(items.value))
	alert('saved')
}
const restore = () => {
	const data = JSON.parse(localStorage.getItem('assets'))
	items.value.splice(0)
	graphData.value.datasets.splice(0)
	graphData.value.labels.splice(0)
	data.forEach((d) => {
		items.value.push(d)
	})
	update()
}
</script>
<style scoped>
.items {
	width: 1200px;
}
</style>
