import { noop } from 'lodash'
import type { Plugin, CustomRTE } from 'grapesjs'
import type CKE from 'ckeditor4'
import TagVue from '@/Components/Tag.vue'
import { h } from 'vue' // Import Vue
import Vue from 'vue' // Import Vue

function getMergeTagData() {
    return axios.get(route('org.models.mailshot.custom.text'))
        .then(response => response.data)
        .catch(error => {
            console.error(error)
            return []
        })
}


export type PluginOptions = {
    options?: CKE.config
    ckeditor?: CKE.CKEditorStatic | string
    position?: 'left' | 'center' | 'right'
    customRte?: Partial<CustomRTE>
    onToolbar?: (toolbar: HTMLElement) => void
    RTE?: noop
}

const isString = (value: any): value is string => typeof value === 'string'

const loadFromCDN = (url: string) => {
    const scr = document.createElement('script')
    scr.src = url
    document.head.appendChild(scr)
    return scr
}

const forEach = <T extends HTMLElement = HTMLElement>(items: Iterable<T>, clb: (item: T) => void) => {
    [].forEach.call(items, clb)
}

const stopPropagation = (ev: Event) => ev.stopPropagation();

;

async function dataFeed(opts, callback) {
    const mergeTagsOption = await getMergeTagData()
    let SetData = []
    var matchProperty = 'label',
        data = mergeTagsOption.filter(function (item) {
            return item[matchProperty].includes(opts.query)
        })

    for (const set of data) {
        set.id = set.value
        set.name = set.label
        SetData.push(set)
    }
    callback(data)
}

const plugin: Plugin<PluginOptions> = async (editor, options = {}) => {
    const mergeTagsOption = await getMergeTagData()
    const opts: Required<PluginOptions> = {
        options: {
            mentions: [
                {
                    feed: dataFeed,
                    itemTemplate:
                        '<li data-id="{id}">' +
                        '<span class="label">{label}</span>' +
                        '</li>',
                    outputTemplate: `<a>[{label}]</a>`,
                    marker: '@',
                    minChars: 0
                },
            ],
            ...options.options,
        },
        customRte: {},
        position: 'left',
        ckeditor: 'https://cdn.ckeditor.com/4.21.0/standard-all/ckeditor.js',
        onToolbar: () => { },
    }

    let ck: CKE.CKEditorStatic | undefined
    const { ckeditor } = opts
    const hasWindow = typeof window !== 'undefined'
    let dynamicLoad = false

    // Check and load CKEDITOR constructor
    if (ckeditor) {
        if (isString(ckeditor)) {
            if (hasWindow) {
                dynamicLoad = true
                const scriptEl = loadFromCDN(ckeditor)
                scriptEl.onload = () => {
                    ck = window.CKEDITOR
                }
            }
        } else if (ckeditor.inline!) {
            ck = ckeditor
        }
    } else if (hasWindow) {
        ck = window.CKEDITOR
    }

    const updateEditorToolbars = () => setTimeout(() => editor.refresh(), 0)
    const logCkError = () => {
        editor.log('CKEDITOR instance not found', { level: 'error' })
    }

    if (!ck && !dynamicLoad) {
        return logCkError()
    }

    const focus = (el: HTMLElement, rte?: CKE.editor) => {
        if (rte?.focusManager?.hasFocus) return
        el.contentEditable = 'true'
        rte?.focus()
        updateEditorToolbars()
    }


    editor.setCustomRte({
        getContent(el, rte: CKE.editor) {
            return rte.getData()
        },

        enable(el, rte?: CKE.editor) {
            // If already exists I'll just focus on it
            if (rte && rte.status != 'destroyed') {
                focus(el, rte)
                return rte
            }

            if (!ck) {
                logCkError()
                return
            }

            // Seems like 'sharedspace' plugin doesn't work exactly as expected
            // so will help hiding other toolbars already created
            const rteToolbar = editor.RichTextEditor.getToolbarEl()
            forEach(rteToolbar.children as Iterable<HTMLElement>, (child) => {
                child.style.display = 'none'
            })

            // Check for the mandatory options
            const ckOptions = { ...opts.options }
            const plgName = 'sharedspace'

            if (ckOptions.extraPlugins) {
                if (typeof ckOptions.extraPlugins === 'string') {
                    ckOptions.extraPlugins += `,${plgName}`
                } else if (Array.isArray(ckOptions.extraPlugins)) {
                    (ckOptions.extraPlugins as string[]).push(plgName)
                }
            } else {
                ckOptions.extraPlugins = plgName
            }

            if (!ckOptions.sharedSpaces) {
                ckOptions.sharedSpaces = { top: rteToolbar }
            }

            ck.dialog?.addUIElement('tag', {
                build: (a, b, g) => {

                },
            })


            /*    ck.on('dialogDefinition', function (ev) {
                 // Take the dialog name and its definition from the event data.
                 var dialogName = ev.data.name;
                 var dialogDefinition = ev.data.definition;
             
                 // Check if the definition is from the dialog window you are interested in (the "Link" dialog window).
                 if (dialogName == 'link') {
                     // Get a reference to the "Link Info" tab.
                     var infoTab = dialogDefinition.getContents('info');
             
                     // Set the default value for the URL field.
                     var urlField = infoTab.get('url');
                     urlField['default'] = 'www.example.com';
             
                     // Add a new tab called "Tag".
                     dialogDefinition.addContents({
                         id: 'tag',
                         label: 'Tag',
                         elements: [
                             {
                                 type: 'text',
                                 id: 'tag',
                                 label: 'Tag Text',
                                 'default': '',
                                 setup: function (widget) {
                                     // Set the initial value of the tag text when editing an existing link.
                                     console.log('widget', widget);
                                 },
                                 commit: function (widget) {
                                   // Get the current value of the tag input.
                                   var tagValue = this.getValue();
                               
                                   // Ensure that widget.data object exists
                                   widget.data = widget.data || {};
                               
                                   // Ensure that widget.data.link object exists
                                   widget.data.link = widget.data.link || {};
                               
                                   // Update the 'data-tag' attribute in the link object.
                                   widget.data.link['data-tag'] = tagValue;
                               
                                   // Get the current href value and update it with the 'data-tag' attribute.
                                   var currentHref = widget.data.link.href || '';
                                   var updatedHref = 'http://www.example.com';  // Replace with your desired href value
                                   console.log(currentHref,widget)
                                   if (currentHref) {
                                       updatedHref += '?data-tag=' + encodeURIComponent(tagValue);
                                   }
                               
                                   // Set the updated href value.
                                   widget.data.link.href = updatedHref;
                               
                                   console.log('Updated href with data-tag attribute:', updatedHref);
                               }
                               
                             }
                         ]
                     });
                 }
             }); */




            /*  ck.on('addUIElement', function (ev) {
               // Take the dialog name and its definition from the event data.
               console.log('Event triggered - addUIElement:', ev);
             
               // Create a new Vue instance
               const vueInstance = h(TagVue);
             
               // Mount the Vue instance to an element
               const container = document.createElement('div'); // Create a container element
               document.body.appendChild(container); // Append the container to the body
               vueInstance.mount(container); // Mount the Vue instance to the container
             });; */

            console.log(ck)
            rte = ck!.inline(el, ckOptions)
            console.log(rte)


            // Make click event propogate
            rte.on('contentDom', () => {
                const editable = rte!.editable()
                editable.attachListener(editable, 'click', () => el.click())
            })

            // The toolbar is not immediatly loaded so will be wrong positioned.
            // With this trick we trigger an event which updates the toolbar position
            rte.on('instanceReady', () => {
                const toolbar = rteToolbar.querySelector<HTMLElement>(`#cke_${rte!.name}`)
                if (toolbar) {
                    toolbar.style.display = 'block'
                    opts.onToolbar(toolbar)
                }
                // Update toolbar position
                editor.refresh()
                // Update the position again as the toolbar dimension might have a new changed
                updateEditorToolbars()
            })

            // Prevent blur when some of CKEditor's element is clicked
            rte.on('dialogShow', () => {
                const els = document.querySelectorAll<HTMLElement>('.cke_dialog_background_cover, .cke_dialog_container')
                forEach(els, (child) => {
                    child.removeEventListener('mousedown', stopPropagation)
                    child.addEventListener('mousedown', stopPropagation)
                })
            })

            // On ENTER CKEditor doesn't trigger `input` event
            rte.on('key', (ev: any) => {
                ev.data.keyCode === 13 && updateEditorToolbars()
            })

            rte.ui.addButton('customTag', {
                label: 'Merge Tag',
                command: 'customTag',
                toolbar: 'insert',
                className: 'custom-tag-button',
                icon: false
            })

            CKEDITOR.dialog.add('customTagDialog', function (editor) {
                var dialog = null // Variabel untuk menyimpan referensi dialog

                return {
                    title: 'Custom Tag Options',
                    minWidth: 200,
                    minHeight: 100,
                    buttons: [],
                    contents: [{
                        id: 'tab1',
                        label: 'Tab 1',
                        title: 'Tab 1',
                        expand: true,
                        padding: 0,
                        elements: [{
                            type: 'html',
                            html: '<ul id="customTagList"></ul>',
                            onLoad: function () {
                                var listContainer = document.getElementById('customTagList')
                                mergeTagsOption.forEach(function (item) {
                                    var listItem = document.createElement('li')
                                    var button = document.createElement('button')
                                    button.textContent = item.label
                                    button.addEventListener('click', function () {
                                        editor.insertHtml(`<span data-gjs-editable="false"  id="${item.value}">[${item.label}]</span>`)
                                        if (dialog) {
                                            dialog.hide() // Menutup dialog saat item diklik
                                        }
                                    })

                                    // CSS styles
                                    listItem.style.textAlign = 'center' // Membuat teks berada di tengah
                                    listItem.style.marginBottom = '10px' // Membuat jarak antar item
                                    button.style.backgroundColor = 'transparent' // Menghapus latar belakang tombol
                                    button.style.border = 'none' // Menghapus border tombol
                                    button.style.cursor = 'pointer' // Mengubah kursor saat di atas tombol
                                    button.style.transition = 'color 0.3s ease' // Efek transisi

                                    // Efek hover
                                    button.addEventListener('mouseover', function () {
                                        button.style.color = 'blue' // Warna biru saat hover
                                    })
                                    button.addEventListener('mouseout', function () {
                                        button.style.color = 'black' // Kembali ke warna hitam saat tidak hover
                                    })

                                    listItem.appendChild(button)
                                    listContainer.appendChild(listItem)
                                })
                            }
                        }]
                    }],
                    onShow: function () {
                        dialog = this // Menyimpan referensi dialog saat dialog ditampilkan
                    }
                }
            })



            rte.addCommand('customTag', {
                exec: function (editor) {
                    editor.openDialog('customTagDialog')
                }
            })

            rte.execCommand('toolbarCollapse')
            rte.execCommand('toolbarExpand')

            focus(el, rte)

            return rte
        },

        disable(el, rte?: CKE.editor) {
            el.contentEditable = 'false'
            rte?.focusManager?.blur(true)
        },

        ...opts.customRte,
    })

    // Update RTE toolbar position
    editor.on('rteToolbarPosUpdate', (pos: any) => {
        const { elRect } = pos

        switch (opts.position) {
            case 'center':
                pos.left = (elRect.width / 2) - (pos.targetWidth / 2)
                break
            case 'right':
                pos.left = ''
                pos.right = 0
                break
        }
    })
}

export default plugin;


