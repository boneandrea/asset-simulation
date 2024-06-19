<template>
	<div class="chart-container" style="width: 1200px">
		<canvas ref="canvasRef"></canvas>
	</div>
	<hr />
	<div @keyup.enter="update">
		<div class="input-group mb-3">
			<input v-model="config.s" type="number" class="form-control" min="0" placeholder="sony yield % (e.g.: 10)" />
		</div>
		<div class="input-group mb-3">
			<input v-model="config.r" type="number" class="form-control" min="0" placeholder="楽天 yield % (e.g.: 11)" />
		</div>
		<div class="input-group mb-3">
			<input v-model="config.d" type="number" class="form-control" step="10" min="0" placeholder="楽天 年間積立" />
		</div>
		<div class="input-group mb-3">
			<input v-model="config.S" type="number" min="0" class="form-control" placeholder="sony 取り崩し/month" />
		</div>
		<div class="input-group mb-3">
			<input v-model="config.R" type="number" class="form-control" min="0" placeholder="楽天 取り崩し/month" />
		</div>
		<div class="input-group mb-3">
			<input v-model="config.year" type="number" class="form-control" min="55" placeholder="楽天終了年齢" />
		</div>
		<div class="input-group mb-3">
			<input
				v-model="config.rate_later.sony"
				type="number"
				min="0"
				class="form-control"
				placeholder="低減yield % (sony)"
			/>
		</div>
		<div class="input-group mb-3">
			<input
				v-model="config.rate_later.rakuten"
				type="number"
				min="0"
				class="form-control"
				placeholder="低減yield % (rakuten)"
			/>
		</div>
		<div class="input-group mb-3">
			<input
				v-model="config.year_change_rate"
				type="number"
				class="form-control"
				placeholder="低減レートになる年齢"
				min="50"
			/>
		</div>
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
		asset_start: 1000,
		pay_per_month: 20,
		withdraw: 20,
		year_change_rate: 62,
		end_age: 60,
	},
	{
		name: 'rakuten',
		rate1: 11,
		rate2: 8,
		start_year: 2019,
		asset_start: 2900,
		pay_per_month: 10,
		withdraw: 60,
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
		withdraw: 80,
		year_change_rate: 62,
		end_age: 57,
	},
])
const change = (e, a, b) => {
	console.log(e, a)
}
const config = ref({
	s: 10,
	r: 11,
	S: 20,
	R: 50,
	year: 57,
	d: 240,
	rate_later: {
		sony: 6,
		rakuten: 6,
	},
	year_change_rate: 65,
})
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
		{
			label: 'total',
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
			//graphData.value.datasets.splice(0)
			graphData.value.labels.splice(0)
			r.slice(2, 3).forEach((data, index) => {
				console.log(data)
				data.data.forEach((e) => (xaxis[e.year] = 1))

				const labels = Object.keys(xaxis)
					.map((e) => parseInt(e))
					.filter((year) => year >= 2024)

				data.data.forEach((d) => graphData.value.labels.push(`${d.year}/${d.year - BONE_AT}`))

				graphData.value.datasets[index].label = data.name
				graphData.value.datasets[index].data.splice(0)
				data.data
					.filter((e) => e.year >= 2024)
					.forEach((e) => {
						graphData.value.datasets[index].data[labels.indexOf(e.year)] = e.asset
					})
				console.log(data.data)
				console.log(graphData)
				console.log(graphData.value)
				graphData.value.datasets[3].label = 'TOTAL'
				labels.forEach((y, i) => {
					graphData.value.datasets[index].data[i] += 1
				})

				// 消費
				/* data.value.datasets[3].data.splice(0, data.value.datasets[3].data.length)
				            if (y - BONE_AT >= config.value.year) {
					          data.value.datasets[3].data[i] = 0
					          if (r[name].datasets[0].data[i] > 0) data.value.datasets[3].data[i] += items[i].withdraw * 12
			              } */
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
