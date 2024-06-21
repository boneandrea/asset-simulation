<template>
	<div class="row">
		<div class="col-md-2">
			<div class="input-group mb-3">
				<button class="btn btn-danger" :disable="rendered" @click="remove">
					<i class="bi bi-trash"></i>
				</button>
				<input class="form-control" v-model="data.name" placeholder="商品名" :class="{ 'is-invalid': !data.name }" />
			</div>
		</div>
		<div class="col-ms-10">
			<div class="input-group mb-3">
				<input
					v-model="data.asset_start"
					type="number"
					class="form-control"
					min="55"
					placeholder="初期投資額(万)"
					title="初期投資額(万)"
				/>
				<input
					v-model="data.rate1"
					type="number"
					class="form-control"
					min="0"
					placeholder="利回り1(%)"
					title="利回り1"
				/>
				<input
					v-model="data.rate2"
					type="number"
					class="form-control"
					min="0"
					placeholder="利回り2(%)"
					title="利回り2"
				/>
				<input
					v-model="data.pay_per_month"
					type="number"
					class="form-control"
					min="0"
					placeholder="積立/月(万)"
					title="積立/月(万)"
				/>
				<input
					v-model="data.start_year"
					type="number"
					class="form-control"
					min="50"
					placeholder="投資開始年"
					title="投資開始年"
				/>
				<input
					v-model="data.end_age"
					type="number"
					class="form-control"
					min="55"
					placeholder="投資終了年齢"
					title="投資終了年齢"
				/>
				<input
					v-model="data.year_change_rate"
					type="number"
					class="form-control"
					min="50"
					placeholder="利回り切替年齢"
					title="利回り切替年齢"
				/>
				<input
					v-model="data.withdraw"
					type="number"
					class="form-control"
					min="0"
					placeholder="取崩/月(万)"
					title="取崩/月(万)"
				/>
			</div>
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
const emit = defineEmits(['remove'])
const remove = () => {
	emit('remove', props.data)
}
</script>
