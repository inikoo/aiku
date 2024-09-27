export const webBlock = (webpage: String) => {
    let eventData = null  // To store event data from the channel
    
    return {
      eventData,
      actions: {
        unsubscribe() {
          window.Echo.leave(`webpage.${webpage}.preview`);
        },
        subscribe() {
          window.Echo.private(`webpage.${webpage}.preview`)
            .listen(".WebpagePreview", (e) => {
              eventData = e;
          });
        },
      },
    };
  };
  