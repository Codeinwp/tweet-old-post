<template>
  <div
    v-on-clickaway="closeDropdown"
    class="form-autocomplete"
    style="width: 100%;"
  >
    <!-- autocomplete input container -->
    <div
      class="form-autocomplete-input form-input"
      :class="is_focused"
    >
      <!-- autocomplete chips -->
      <label
        v-for="( option, index ) in selected"
        class="chip"
      >
        {{ option.name }}
        <a
          href="#"
          class="btn btn-clear"
          aria-label="Close"
          role="button"
          @click.prevent="removeSelected(index)"
        />
      </label>
			
      <!-- autocomplete real input box -->
      <input
        ref="search"
        v-model="search"
        style="height: 1.0rem;"
        class="form-input"
        type="text"
        :placeholder="autocomplete_placeholder"
        :disabled="is_disabled"
        @click="magic_flag = true"
        @focus="magic_flag = true"
        @keyup="magic_flag = true"
        @keydown.8="popLast()"
        @keydown.38="highlightItem(true)"
        @keydown.40="highlightItem()"
      >
    </div>
		
    <!-- autocomplete suggestion list -->
    <ul
      ref="autocomplete_results"
      class="menu"
      :class="is_visible"
      style="overflow-y: scroll; max-height: 120px"
    >
      <!-- menu list chips -->
      <li
        v-for="( option, index ) in options"
        v-if="filterSearch(option)"
        class="menu-item"
      >
        <a
          href="#"
          @click.prevent="addToSelected(index)"
          @keydown.38="highlightItem(true)"
          @keydown.40="highlightItem()"
        >
          <div class="tile tile-centered">
            <div
              class="tile-content"
              v-html="markMatch(option.name, search)"
            />
          </div>
        </a>
      </li>
      <li v-if="has_results">
        <a href="#">
          <div class="tile tile-centered">
            <div class="tile-content"><i>{{ labels.multiselect_not_found }}"{{ search }}" ...</i></div>
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

	export default {
		name: 'MultipleSelect',
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
				default: 'Please select something',
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
            },
            is_pro_version: {
                default: false,
                type: Boolean
            },
            apply_limit: {
                default: false,
                type: Boolean
			}
		},
		data: function () {
			return {
				search: '',
				highlighted: -1,
				no_results: false,
				labels: this.$store.state.labels.general,
				upsell_link: ropApiSettings.upsell_link,
				magic_flag: false,
				rand: 0
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
		watch: {
			search: function (val) {
				this.$emit('update', val)
            },
            selected: function (val) {
                this.$emit( 'display-limiter-notice', this.selected.length)
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
		},
		updated(){

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
		},
		created() {
			let selected_items_no = 0;
			for (let selection of this.selected) {
				if (selection.selected) {
					let index = 0;
					for (let option of this.options) {
						if (option.value === selection.value) {
							this.options[index].selected = selection.selected;
							selected_items_no++
						}
						index++
					}

				}
			}

			this.rand = Math.round(Math.random() * 1000);
			let index = 0
			for (let option of this.options) {
				this.options[index].selected = false;
				index++
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
				if (this.is_disabled) {
					return;
				}

                if (false === this.limit_selection()) {
                    return;
                }

                let newSelection = this.options[index]
                newSelection.selected = true
                this.selected.push(newSelection)
                this.$refs.search.focus()
                this.magic_flag = false
                this.search = ''
                this.changedSelection(this.selected)
            },
            removeSelected(index) {
                if (this.is_disabled) {
                    return;
                }
                this.selected.splice(index, 1)
                this.$refs.search.focus()
                this.magic_flag = false
                this.search = ''
                this.changedSelection(this.selected)
            },
            limit_selection() {
                if(true === this.apply_limit){
                    if (false === this.is_pro_version && this.selected.length > 3) {
                        this.$refs.search.focus();
                        this.magic_flag = false;
                        this.search = '';

                        return false;
                    }
                }
                return true;
			}
		}
	}
</script>