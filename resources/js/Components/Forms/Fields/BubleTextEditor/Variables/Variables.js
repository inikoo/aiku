import Mention  from "@tiptap/extension-mention"
import { Plugin } from "prosemirror-state"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { PluginKey } from '@tiptap/pm/state'
import { mergeAttributes } from '@tiptap/core'
// import { Node } from '@tiptap/core'

export const MentionPluginKey = new PluginKey('mention')

const CustomLink = Mention.extend({
    addOptions() {
        return {
          HTMLAttributes: {},
          renderText({ options, node }) {
            return `${options.suggestion.char}${node.attrs.label ?? node.attrs.id}`
          },
          deleteTriggerWithBackspace: false,
          renderHTML({ options, node }) {
            return [
              'span',
              mergeAttributes(this.HTMLAttributes, options.HTMLAttributes),
              `${options.suggestion.char}  ${ node.attrs.label ?? node.attrs.id + " }}"}`,
            ]
          },
          suggestion: {
            char: '{{',
            pluginKey: MentionPluginKey,
            command: ({ editor, range, props }) => {
              // increase range.to by one when the next node is of type "text"
              // and starts with a space character
              const nodeAfter = editor.view.state.selection.$to.nodeAfter
              const overrideSpace = nodeAfter?.text?.startsWith(' ')
    
              if (overrideSpace) {
                range.to += 1
              }
    
              editor
                .chain()
                .focus()
                .insertContentAt(range, [
                  {
                    type: this.name,
                    attrs: props,
                  },
                  {
                    type: 'text',
                    text: ' ',
                  },
                ])
                .run()
    
              // get reference to `window` object from editor element, to support cross-frame JS usage
              editor.view.dom.ownerDocument.defaultView?.getSelection()?.collapseToEnd()
            },
            allow: ({ state, range }) => {
              const $from = state.doc.resolve(range.from)
              const type = state.schema.nodes[this.name]
              const allow = !!$from.parent.type.contentMatch.matchType(type)
    
              return allow
            },
          },
        }
      },
})




export default CustomLink
