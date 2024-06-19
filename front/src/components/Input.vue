<template>
	<div class="row">
		<label>{{ data.name }}</label>
		<div class="input-group mb-3">
			<input
				v-model="data.asset_start"
				type="number"
				class="form-control"
				min="55"
				placeholder="初期投資額"
				title="初期投資額"
				@input="change"
			/>
			<input
				v-model="data.rate1"
				type="number"
				class="form-control"
				min="0"
				placeholder="利回り1(%)"
				title="利回り1"
				@input="change"
			/>
			<input
				v-model="data.rate2"
				type="number"
				class="form-control"
				min="0"
				placeholder="利回り2(%)"
				title="利回り2"
				@input="change"
			/>
			<input
				v-model="data.pay_per_month"
				type="number"
				class="form-control"
				min="0"
				placeholder="積立/month(万)"
				title="積立/month(万)"
				@input="change"
			/>
			<input
				v-model="data.start_year"
				type="number"
				class="form-control"
				min="50"
				placeholder="投資開始年"
				title="投資開始年"
				@input="change"
			/>
			<input
				v-model="data.end_age"
				type="number"
				class="form-control"
				min="55"
				placeholder="投資終了年齢"
				title="投資終了年齢"
				@input="change"
			/>
			<input
				v-model="data.year_change_rate"
				type="number"
				class="form-control"
				min="50"
				placeholder="rate切替年齢"
				title="rate切替年齢"
				@input="change"
			/>
			<input
				v-model="data.withdraw"
				type="number"
				class="form-control"
				min="0"
				placeholder="取り崩し/month(万)"
				title="取り崩し/month(万)"
				@input="change"
			/>
		</div>
	</div>
</template>
<script setup>
import { ref } from 'vue'
const props = defineProps({
	data: {
		type: Object,
		required: true,
	},
	id: {
		type: Number,
		required: true,
	},
})

const data = ref(props.data)
const emit = defineEmits(['change'])
function change() {
	emit('change', props.data)
}
</script>
