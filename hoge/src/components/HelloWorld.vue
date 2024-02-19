<template>
    <div class="chart-container" style="width: 1200px">
        <canvas ref="canvasRef"></canvas>
    </div>
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
        <button class="btn btn-primary" :disable="rendered" @click="update">Update</button>
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
     S:30,
     R:50,
     year:57,
 })
 const canvasRef = ref(null);
 const rendered = ref(false);
 const data=ref({
     labels: ['1月', '2月', '３月', '４月', '5月', '6月', '７月'],
     datasets: [
         {
             label: 'データ',
             data: [],
             fill: false,
             borderColor: 'rgb(75, 192, 192)',
             tension: 0.1
         },
         {
             label: 'データ',
             data: [],
             fill: false,
             borderColor: '#a23456',
             tension: 0.1
         },
         {
             label: 'total',
             data: [],
             fill: false,
             borderColor: '#E74C3C',
             tension: 0.1
         }
     ]
 })
 const init=()=>{
     if (canvasRef.value === null) return;
     const canvas = canvasRef.value.getContext("2d");
     if (canvas === null) return;
     rendered.value=false
     chart.value = new Chart(canvas, {
         type: "line",
         data: data.value
     });
     rendered.value=true
 }
 const chart=ref(null)
 onMounted(() => {
     init()
 })
 const update=()=>{
     chart.value.destroy()
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
             if (r.status === 'error') {
                 throw new Error(r['reason'])
             }

             // set labels
             const xaxis={}
             r.rakuten.data.forEach(e=>xaxis[e.year]=1)
             r.sony.data.forEach(e=>xaxis[e.year]=1)

             const labels=Object.keys(xaxis).map(e=>parseInt(e)).filter(year=>year >= 2024)
             data.value.labels.splice(0, data.value.labels.length)
             labels.forEach(y=>data.value.labels.push(`${y}/${y-1973}`))

             data.value.datasets[0].label=r.rakuten.name
             data.value.datasets[0].data.splice(0, data.value.datasets[0].data.length)
             r.rakuten.data.filter(e=>e.year >= 2024).forEach(e=>{
                 data.value.datasets[0].data[labels.indexOf(e.year)] = e.asset
             })

             data.value.datasets[1].label=r.sony.name
             data.value.datasets[1].data.splice(0, data.value.datasets[1].data.length)
             r.sony.data.filter(e=>e.year >= 2024).forEach(e=>{
                 data.value.datasets[1].data[labels.indexOf(e.year)] = e.asset
             })

             data.value.datasets[2].data.splice(0, data.value.datasets[2].data.length)
             data.value.datasets[2].label="TOTAL"
             labels.forEach((y,i)=>{
                 data.value.datasets[2].data[i] =
                     (data.value.datasets[0].data[i] ?? 0) +
                     (data.value.datasets[1].data[i] ?? 0)
             })
             init()
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
 canvas{
     width: 100%
 }
</style>
