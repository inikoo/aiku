export const socketWeblock = (webpage) => {
    let eventData = null;  // To store event data from the channel

    return {
        eventData,
        actions: {
            unsubscribe() {
                window.Echo.leave(`webpage.${webpage}.preview`);
            },
            subscribe: (callback) => {
                const channel = window.Echo.private(`webpage.${webpage}.preview`)
                    .listen('.WebpagePreview', (event) => {
                        if (event && event.webpage) {
                            eventData = { ...event.webpage };
                            if (callback) {
                                callback(eventData);
                            }
                        }
                    });
            },
        },
    };
};


  export const SocketHeaderFooter = (website: String) => {
    let eventData = null

    return {
      eventData,
      actions: {
        unsubscribe() {
          window.Echo.leave(`header-footer.${website}.preview`);
        },
        subscribe: (callback) => {
          const channel = window.Echo.private(`header-footer.${website}.preview`)
              .listen('.WebpagePreview', (event) => {
                  console.log('data', event)
                  if (event && event.webpage) {
                      eventData = { ...event.webpage };
                      if (callback) {
                          callback(eventData);
                      }
                  }
              });

              console.log('dfd',channel)
      },
      },
    };
  };

