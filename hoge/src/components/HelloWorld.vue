<template>
    <canvas ref="canvasRef" />
    <hr>
    <div class="input-group mb-3">
        <input v-model="config.s" type="text" class="form-control" placeholder="sony rate(e.g.: 1.10)" aria-label="Username" aria-describedby="basic-addon1">
    </div>
    <div class="input-group mb-3">
        <input v-model="config.r" type="text" class="form-control" placeholder="楽天 rate(e.g.: 1.11)" aria-label="Username" aria-describedby="basic-addon1">
    </div>
    <div class="input-group mb-3">
        <input v-model="config.d" type="text" class="form-control" placeholder="楽天 年間積立" aria-label="Username" aria-describedby="basic-addon1">
    </div>
    <div class="input-group mb-3">
        <input v-model="config.S" type="text" class="form-control" placeholder="sony 取り崩し/month" aria-label="Username" aria-describedby="basic-addon1">
    </div>
    <div class="input-group mb-3">
        <input v-model="config.R" type="text" class="form-control" placeholder="楽天 取り崩し/month" aria-label="Username" aria-describedby="basic-addon1">
    </div>
    <div class="input-group mb-3">
        <input v-model="config.year" type="text" class="form-control" placeholder="楽天終了年齢" aria-label="Username" aria-describedby="basic-addon1">
    </div>
    <div class="input-group mb-3">
        <button class="btn btn-primary" @click="update">Update</button>
    </div>
</template>
<script setup>
 defineProps({
     msg: {
         type: String,
         required: true
     }
 })
 import { ref, onMounted } from "vue";
 import { Chart, registerables } from "chart.js"
 Chart.register(...registerables)

 const config=ref({
     s:1.10,
     r:1.11,
     S:20,
     R:50,
     year:57,
 })
 const canvasRef = ref(null);
 const data=ref({
     labels: ['1月', '2月', '３月', '４月', '5月', '6月', '７月'],
     datasets: [{
         label: 'データ',
         data: [65, 59, 80, 81, 56, 55, 40],
         fill: false,
         borderColor: 'rgb(75, 192, 192)',
         tension: 0.1
     }]
 })
 const init=()=>{
     if (canvasRef.value === null) return;
     const canvas = canvasRef.value.getContext("2d");
     if (canvas === null) return;
     const c = new Chart(canvas, {
         type: "line",
         data: {
             labels: ["Red", "Blue", "Yellow", "Green", "Purple", "Orange"],
             datasets: [{
                 label: "てすと",
                 data: [65, 59, 80, 81, 56, 55, 40],
             }]
         }
     });
     console.log(c)
 }

 onMounted(() => {
     init()
 })
 const update=()=>{
     console.log(data.value, config.value)
     const api="http://localhost:8888/cal.php"
     fetch(api, {
         method: 'POST',
         headers: { 'content-type': 'application/json' },
         body: JSON.stringify({
             data: config.value
         }),
     })
         .then((response) => {
             return response.json()
         })
         .then((r) => {
             console.log(r)
             console.log(data.value.datasets[0].data)
             data.value.datasets[0].dataa.push(50)
             //data.value.datasets[0].data.splice(0, data.value.datasets[0].data.length-1)
             console.log(r.rakuten.data)
             //data.value.datasets.data=r.rakuten.data.map(e=>e.asset)

             if (r.status === 'error') {
                 throw new Error(r['reason'])
             }
         })
         .catch((e) => {
             console.error(e)
             alert(e)
         })
         .finally(() => {
         })
 }
</script>
<style scoped>
</style>
