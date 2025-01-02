<script setup lang="ts"></script>
<template>
	<button className="px-6 py-2 rounded-md relative radial-gradient">
		<span
			className="text-neutral-100 tracking-wide font-light h-full w-full block relative linear-mask">
			Button
		</span>
	</button>
</template>

<style>
.radial-gradient {
	background: radial-gradient(circle at 50% 0%, rgba(250, 250, 250, 0.05) 0%, transparent 60%),
		rgba(15, 15, 15, 1);
}

.linear-mask {
	mask-image: linear-gradient(-75deg, white 20%, transparent 30%, white 100%);
	-webkit-mask-image: linear-gradient(-75deg, white calc(var(--x)+20%), transparent calc(var(--x)+ 30%), white calc(var(--x) + 100%));
}
</style>
