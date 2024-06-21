<template>
	<div class="chart-container" style="width: 1200px">
		<canvas ref="canvasRef"></canvas>
	</div>
	<hr />
	<div class="items">
		<input
			v-model="items.bone_at"
			type="number"
			class="form-control w-25"
			min="1970"
			placeholder="生まれ年"
			title="生まれ年"
			:class="{ 'is-invalid': !items.bone_at }"
		/>
		<hr />
		<div @keyup.enter="update">
			<Input v-for="(item, index) in items.items" :id="index" :data="item" @change="change" @remove="remove" />
		</div>
		<div class="row">
			<div class="col">
				<button class="w-25 btn btn-primary" @click="add">Add new</button>
			</div>
			<div class="col">
				<button class="btn btn-primary w-25" :disabled="items.bone_at < 1970" @click="update">Update</button>
			</div>
		</div>
		<hr />
		<div class="row md-1">
			<div class="col">
				<button class="btn mr-3 btn-info w-25" @click="save">Save</button>
			</div>
			<div class="col">
				<button class="btn mr-3 btn-info w-25" @click="restore">Restore</button>
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
	items.value.items.forEach((i) => {
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
	const index = items.value.items.findIndex((item) => item.name === e.name)
	if (index !== -1) {
		items.value.items.splice(index, 1)
		graphData.value.datasets.splice(index, 1)
		graphData.value.labels.splice(index, 1)
	}
}
const add = () => {
	items.value.items.push({
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
			labels.forEach((d) => graphData.value.labels.push(`${d}/${d - items.value.bone_at}`))

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
						if (e.year >= items.value.bone_at + items.value.items[index].end_age) {
							const sum =
								e.asset > items.value.items[index].withdraw * 12 ? items.value.items[index].withdraw * 12 : e.asset
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
	items.value.items.splice(0)
	graphData.value.datasets.splice(0)
	graphData.value.labels.splice(0)
	data.items.forEach((d) => {
		items.value.items.push(d)
	})
	items.value.bone_at = data.bone_at
	update()
}
</script>
<style scoped>
.items {
	width: 1200px;
}
</style>
