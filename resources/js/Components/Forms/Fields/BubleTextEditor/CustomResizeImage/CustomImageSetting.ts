import Image from '@tiptap/extension-image';
import { imageAlignmentStyle, imageWidthStyle, toImageWidth } from './imageLayout';

const RESIZE_HANDLE_ATTRIBUTE = 'data-image-resize-handle';
const IMAGE_ELEMENT_ATTRIBUTES = ['src', 'alt', 'title'];

const RESIZE_HANDLE_CORNERS = [
  { transform: 'translate(-50%, -50%)', top: '0%', left: '0%' },
  { transform: 'translate(50%, -50%)', top: '0%', right: '0%' },
  { transform: 'translate(-50%, 50%)', bottom: '0%', left: '0%' },
  { transform: 'translate(50%, 50%)', bottom: '0%', right: '0%' },
];

export const CustomImage = Image.configure({ inline: true }).extend({
  addAttributes() {
    return {
      ...this.parent?.(),
      float: {
        default: 'none',
        renderHTML: ({ float }) => ({ style: imageAlignmentStyle(float) }),
        parseHTML: element => element.style.float || 'none',
      },
      width: {
        default: null,
        renderHTML: ({ width }) => ({ style: imageWidthStyle(width) }),
        parseHTML: element => element.style.width || null,
      },
    };
  },

  addNodeView() {
    return ({ node, editor, getPos }) => {
      let currentNode = node;
      let resizeHandles: HTMLElement[] = [];

      const $wrapper = document.createElement('div');
      const $container = document.createElement('div');
      const $img = document.createElement('img');

      $wrapper.appendChild($container);
      $container.appendChild($img);

      $img.style.width = '100%';
      $img.style.height = 'auto';
      $img.style.display = 'block';

      const renderImage = () => {
        const { float, width } = currentNode.attrs;

        IMAGE_ELEMENT_ATTRIBUTES.forEach((attribute) => {
          const value = currentNode.attrs[attribute];
          if (value == null) {
            $img.removeAttribute(attribute);
          } else {
            $img.setAttribute(attribute, value);
          }
        });

        const outline = $container.style.outline;
        $container.setAttribute('style', `${imageAlignmentStyle(float)} cursor: pointer; position: relative;`);
        $container.style.outline = outline;

        if (width) {
          $container.style.width = width;
        }
      };

      const widthPercentOfParent = (widthInPixels: number): number | null => {
        const parentWidth = $container.parentElement?.offsetWidth;

        return parentWidth ? (widthInPixels / parentWidth) * 100 : null;
      };

      const saveWidth = () => {
        const position = typeof getPos === 'function' ? getPos() : undefined;
        const widthPercent = widthPercentOfParent($container.offsetWidth);
        if (typeof position !== 'number' || widthPercent === null) return;

        editor
          .chain()
          .command(({ tr }) => {
            tr.setNodeMarkup(position, undefined, { ...currentNode.attrs, width: toImageWidth(widthPercent) });
            return true;
          })
          .setNodeSelection(position)
          .run();
      };

      const createResizeHandle = (corner: Record<string, string>, index: number): HTMLElement => {
        const handleSize = document.documentElement.clientWidth < 768 ? 16 : 9;
        const isLeftCorner = index === 0 || index === 2;
        const $handle = document.createElement('div');

        $handle.setAttribute(RESIZE_HANDLE_ATTRIBUTE, '');
        $handle.setAttribute('style', `
          position: absolute;
          width: ${handleSize}px;
          height: ${handleSize}px;
          border: 1.5px solid #6C6C6C;
          border-radius: 50%;
          background-color: white;
          cursor: ${index % 3 === 0 ? 'nwse-resize' : 'nesw-resize'};
          ${Object.entries(corner).map(([property, value]) => `${property}: ${value}`).join(';')}
        `);

        const startResizing = (startX: number, moveEvent: 'mousemove' | 'touchmove', endEvent: 'mouseup' | 'touchend') => {
          const startWidth = $container.offsetWidth;

          const resize = (event: MouseEvent | TouchEvent) => {
            const clientX = 'touches' in event ? event.touches[0].clientX : event.clientX;
            const draggedDistance = isLeftCorner ? startX - clientX : clientX - startX;
            const widthPercent = widthPercentOfParent(startWidth + draggedDistance);
            if (widthPercent !== null) {
              $container.style.width = toImageWidth(widthPercent);
            }
          };

          const stopResizing = () => {
            document.removeEventListener(moveEvent, resize);
            document.removeEventListener(endEvent, stopResizing);
            saveWidth();
          };

          document.addEventListener(moveEvent, resize, { passive: false });
          document.addEventListener(endEvent, stopResizing);
        };

        $handle.addEventListener('mousedown', (event) => {
          event.preventDefault();
          startResizing(event.clientX, 'mousemove', 'mouseup');
        });

        $handle.addEventListener('touchstart', (event) => {
          event.preventDefault();
          startResizing(event.touches[0].clientX, 'touchmove', 'touchend');
        });

        return $handle;
      };

      const showResizeHandles = () => {
        if (resizeHandles.length || !editor.isEditable) return;

        $container.style.outline = '1px dashed #6C6C6C';
        resizeHandles = RESIZE_HANDLE_CORNERS.map(createResizeHandle);
        resizeHandles.forEach($handle => $container.appendChild($handle));
      };

      const hideResizeHandles = () => {
        $container.style.outline = '';
        resizeHandles.forEach($handle => $handle.remove());
        resizeHandles = [];
      };

      renderImage();

      return {
        dom: editor.isEditable ? $wrapper : $container,
        update: (updatedNode) => {
          if (updatedNode.type !== currentNode.type) return false;

          currentNode = updatedNode;
          renderImage();
          return true;
        },
        selectNode: showResizeHandles,
        deselectNode: hideResizeHandles,
        stopEvent: event => event.target instanceof HTMLElement && event.target.hasAttribute(RESIZE_HANDLE_ATTRIBUTE),
        ignoreMutation: () => true,
        destroy: hideResizeHandles,
      };
    };
  },
});
