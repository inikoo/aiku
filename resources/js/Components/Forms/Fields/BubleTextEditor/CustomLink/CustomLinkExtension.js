import { mergeAttributes, Node } from '@tiptap/core';
import { VueNodeViewRenderer } from '@tiptap/vue-3';
import CustomLink from './CustomLink.vue';
import { find, registerCustomProtocol, reset } from 'linkifyjs';

const ATTR_WHITESPACE = /[\u0000-\u0020\u00A0\u1680\u180E\u2000-\u2029\u205F\u3000]/g;

function isAllowedUri(uri, protocols) {
  const allowedProtocols = ['http', 'https', 'ftp', 'ftps', 'mailto', 'tel', 'callto', 'sms', 'cid', 'xmpp'];
  if (protocols) {
    protocols.forEach(protocol => {
      const nextProtocol = typeof protocol === 'string' ? protocol : protocol.scheme;
      if (nextProtocol) allowedProtocols.push(nextProtocol);
    });
  }
  return !uri || uri.replace(ATTR_WHITESPACE, '').match(
    new RegExp(`^(?:(?:${allowedProtocols.join('|')}):|[^a-z]|[a-z+.-]+(?:[^a-z+.-:]|$))`, 'i')
  );
}

export default Node.create({
  name: 'customLink',
  group: 'inline', // Use 'inline' if the node is used within text content
  inline: true,     // Indicates this is an inline node (for links)
  content: 'inline*', // Allows the node to have inline children
  priority: 1000,

  addOptions() {
    return {
      openOnClick: true,
      linkOnPaste: true,
      autolink: true,
      protocols: [],
      defaultProtocol: 'http',
      HTMLAttributes: {
        target: '_blank',
        rel: 'noopener noreferrer nofollow',
        class: null,
      },
      validate: url => !!url,
    };
  },

  addAttributes() {
    return {
      id: { 
        default: null,
        parseHTML: element => element.getAttribute('id'),
      },
      type: { 
        default: null,
        parseHTML: element => element.getAttribute('type'),
      },
      url: {
        default: null,
        parseHTML: element => element.getAttribute('url'),
      },
      workshop: {
        default: null,
        parseHTML: element => element.getAttribute('workshop'),
      },
      target: {
        default: this.options.HTMLAttributes.target,
      },
      rel: {
        default: this.options.HTMLAttributes.rel,
      },
      class: {
        default: this.options.HTMLAttributes.class,
      },
      content: {
        default: '',
        parseHTML: element => element.textContent || '',
      },
    };
  },

  parseHTML() {
    return [{
      tag: 'CustomLinkExtension',
      getAttrs: dom => {
        const href = dom.getAttribute('url');
        if (!href || !isAllowedUri(href, this.options.protocols)) return false;
        return { content: dom.textContent };
      },
    }];
  },

  renderHTML({ HTMLAttributes }) {
    if (!isAllowedUri(HTMLAttributes.href, this.options.protocols)) {
      return ['CustomLinkExtension', mergeAttributes(this.options.HTMLAttributes, { href: '' }), 0];
    }
    return ['CustomLinkExtension', mergeAttributes(this.options.HTMLAttributes, HTMLAttributes), 0];
  },

  addNodeView() {
    return VueNodeViewRenderer(CustomLink);
  },

});

