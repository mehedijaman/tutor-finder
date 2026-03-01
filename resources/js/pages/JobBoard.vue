<template>
<Head title="Job Board" />

<div class="min-h-screen bg-slate-50 p-6">

<!-- Header -->
<h1 class="text-3xl font-extrabold mb-6">Tutor Job Board</h1>

<!-- Filters -->
<div class="grid md:grid-cols-6 gap-4 mb-8">

<input v-model="search" placeholder="Search..." class="border p-2 rounded" />

<select v-model="subjectFilter" class="border p-2 rounded">
<option>All</option>
<option>Mathematics</option>
<option>English</option>
<option>Biology</option>
</select>

<select v-model="levelFilter" class="border p-2 rounded">
<option>All</option>
<option>Grade 8</option>
<option>High School</option>
<option>University</option>
</select>

<input v-model="minPrice" type="number" placeholder="Min $" class="border p-2 rounded"/>
<input v-model="maxPrice" type="number" placeholder="Max $" class="border p-2 rounded"/>

<select v-model="sortBy" class="border p-2 rounded">
<option>Newest</option>
<option>Highest</option>
</select>

</div>

<!-- Job Listings -->
<div class="space-y-6">

<div v-for="job in filteredJobs" :key="job.id"
class="bg-white p-6 rounded-xl shadow border">

<div class="flex justify-between">

<div>
<h2 class="text-lg font-bold">{{ job.title }}</h2>
<div class="text-sm text-slate-500 mt-1">
{{ job.subject }} • {{ job.level }} • {{ job.type }}
</div>
</div>

<div class="text-right">
<p class="font-bold text-blue-600">${{ job.budget }}/hr</p>
<button
@click="toggleSave(job.id)"
class="text-sm mt-2"
>
{{ savedJobs.includes(job.id) ? 'Saved ✓' : 'Save Job' }}
</button>
</div>

</div>

<div class="mt-4 flex justify-between">

<Link :href="`/jobs/${job.id}`"
class="text-blue-600 font-semibold">
View Details →
</Link>

<button
@click="openApplyModal(job)"
class="bg-blue-600 text-white px-4 py-2 rounded">
Apply Now
</button>

</div>

</div>

</div>

<!-- Apply Modal -->
<div v-if="showModal"
class="fixed inset-0 bg-black/40 flex items-center justify-center">

<div class="bg-white p-8 rounded-xl w-full max-w-md">
<h2 class="text-xl font-bold mb-4">
Apply for {{ selectedJob?.title }}
</h2>

<textarea
placeholder="Write your message..."
class="w-full border p-3 rounded mb-4"
rows="4"
/>

<div class="flex justify-end gap-3">
<button @click="closeModal"
class="px-4 py-2 border rounded">
Cancel
</button>
<button
class="px-4 py-2 bg-blue-600 text-white rounded">
Submit Application
</button>
</div>

</div>
</div>

</div>
</template>