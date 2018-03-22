<template>
	<div class="form-autocomplete" style="width: 100%;" v-on-clickaway="closeDropdown">
		<!-- autocomplete input container -->
		<div class="form-autocomplete-input form-input" :class="is_focused">
			
			<!-- autocomplete chips -->
			<label class="chip" v-for="( option, index ) in selected">
				{{option.name}}
				<a href="#" class="btn btn-clear" aria-label="Close" @click.prevent="removeSelected(index)"
				   role="button"></a>
			</label>
			
			<!-- autocomplete real input box -->
			<input style="height: 1.0rem;" class="form-input" type="text" ref="search" v-model="search"
			       :placeholder="autocomplete_placeholder" @click="magic_flag = true" @focus="magic_flag = true"
			       @keyup="magic_flag = true" @keydown.8="popLast()" @keydown.38="highlightItem(true)"
			       @keydown.40="highlightItem()" :disabled="is_disabled">
		</div>
		
		<!-- autocomplete suggestion list -->
		<ul class="menu" ref="autocomplete_results" :class="is_visible"
		    style="overflow-y: scroll; max-height: 120px">
			<!-- menu list chips -->
			<li class="menu-item" v-for="( option, index ) in options" v-if="filterSearch(option)">
				<a href="#" @click.prevent="addToSelected(index)" @keydown.38="highlightItem(true)"
				   @keydown.40="highlightItem()">
					<div class="tile tile-centered">
						<div class="tile-content" v-html="markMatch(option.name, search)"></div>
					</div>
				</a>
			</li>
			<li v-if="has_results">
				<a href="#">
					<div class="tile tile-centered">
						<div class="tile-content"><i>Nothing found matching "{{search}}" ...</i></div>
					</div>
				</a>
			</li>
		</ul>
	</div>

</template>

<script>
	import {mixin as clickaway} from 'vue-clickaway'

	function containsObject(obj, list) {
		let i
		for (i = 0; i < list.length; i++) {
			if (list[i] === obj) {
				return true
			}
		}
		return false
	}

	module.exports = {
		name: 'multiple-select',
		mixins: [clickaway],
		props: {
			options: {
				default: function () {
					return []
				},
				type: Array
			},
			disabled: {
				default: true,
				type: Boolean
			},
			selected: {
				default: function () {
					return []
				},
				type: Array
			},
			placeHolderText: {
				default: 'Please select somthing',
				type: String
			},
			changedSelection: {
				default: function (data) {
					return data
				},
				type: Function
			},
			dontLock: {
				default: false,
				type: Boolean
			}
		},
		mounted() {
			for (let selection of this.selected) {
				if (selection.selected) {
					let index = 0
					for (let option of this.options) {
						if (option.value === selection.value) {
							this.options[index].selected = selection.selected
						}
						index++
					}
				}
			}

			// this.$emit( 'update', this.search )
		},
		data: function () {
			return {
				search: '',
				highlighted: -1,
				no_results: false,
				magic_flag: false
			}
		},
		watch: {
			search: function (val) {
				this.$emit('update', val)
			}
		},
		computed: {
			is_focused: function () {
				return {
					'is-focused': this.magic_flag === true
				}
			},
			is_visible: function () {
				return {
					'd-none': this.magic_flag === false
				}
			},
			is_one: function () {
				if (!this.dontLock) {
					if (this.options.length === 1 && this.options[0].selected === false) {
						//		this.selected.push(this.options[0])
						return true
					} else if (this.options.length === 1 && this.options[0].selected === true) {
						return true
					}
				}
				return false
			},
			autocomplete_placeholder: function () {
				if (this.selected.length > 0) {
					return ''
				}
				return this.placeHolderText
			},
			is_disabled: function () {
				return !this.disabled;
			},
			has_results: function () {
				let found = 0
				for (var option of this.options) {
					if (this.filterSearch(option)) {
						found++
					}
				}
				if (found) {
					return false
				}
				return true
			}
		},
		methods: {
			closeDropdown: function () {
				this.magic_flag = false
			},
			highlightItem: function (up = false) {
				if (up) {
					this.highlighted--
				} else {
					this.highlighted++
				}
				var size = this.$refs.autocomplete_results.children.length - 1
				if (size < 0) size = 0
				if (this.highlighted > size) this.highlighted = 0
				if (this.highlighted < 0) this.highlighted = size
				this.$refs.autocomplete_results.children[this.highlighted].firstChild.focus()
			},
			popLast: function () {
				if (this.search === '') {
					this.selected.pop()
					this.magic_flag = false
				}
			},
			markMatch: function (value, search) {
				var result = value
				if (value.toLowerCase().indexOf(search.toLowerCase()) !== -1 && search !== '') {
					var rex = new RegExp(search, 'ig')
					result = value.replace(rex, function (match) {
						return '<mark>' + match + '</mark>'
					})
				}
				return result
			},
			filterSearch(element) {
				if (element.name.toLowerCase().indexOf(this.search.toLowerCase()) !== -1 || this.search === '') {
					if (element.selected) {
						return false
					}
					if (containsObject(element, this.selected)) {
						return false
					}
					return true
				}
				return false
			},
			addToSelected(index) {
				let newSelection = this.options[index]
				newSelection.selected = true
				this.selected.push(newSelection)
				this.$refs.search.focus()
				this.magic_flag = false
				this.search = ''
				this.changedSelection(this.selected)
			},
			removeSelected(index) {
				this.selected.splice(index, 1)
				this.$refs.search.focus()
				this.magic_flag = false
				this.search = ''
				this.changedSelection(this.selected)
			}
		}
	}
</script>