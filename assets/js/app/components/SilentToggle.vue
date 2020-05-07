<template>
	<div class="silent-toggle-wrapper">
		<label>
			<input
				type="checkbox"
				id="silent-toggle"
				v-model="isChecked"
			/> <span class="silent-toggle-label">{{label}}</span> <span class="silent-toggle-label-gloss">{{labelGloss}}</span>
		</label>
	</div>
</template>

<script>
	export default {
		computed: {
			isChecked: {
				get() {
					return this.$store.state.silentUpdate
				},

				set( value ) {
					this.$store.commit( 'setSilentUpdate', { value } )
				}
			}
		},

		created() {
			this.isChecked = this.defaultValue
		},

		props: {
			defaultValue: {
				default: false,
				type: Boolean,
			},
			label: {
				type: String,
			},
			labelGloss: {
				default: '(notifications will not be sent to subscribed group members)',
				type: String,
			}
		}
	}
</script>

<style>
	.silent-toggle-wrapper {
		margin-bottom: 10px;
	}

	.silent-toggle-label {
		font-weight: bold;
	}
</style>
