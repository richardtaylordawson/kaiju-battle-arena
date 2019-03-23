const tokenProvider = new Chatkit.TokenProvider({
  url: "https://us1.pusherplatform.io/services/chatkit_token_provider/v1/5947947b-0bd2-440f-b136-f8889f9a181f/token"
});

const currentUserID = "richardtaylordawson";

const chatManager = new Chatkit.ChatManager({
  instanceLocator: "v1:us1:5947947b-0bd2-440f-b136-f8889f9a181f",
  userId: currentUserID,
  tokenProvider: tokenProvider
});

function createUserMessage(message) {
  let div = document.createElement("div");

  div.innerHTML = `
    <div class="row">
      <div class="col-xs-12 pull-right">
        <div class="message-container user-message">
          <p>${message.parts[0].payload.content}</p>
        </div>
      </div>
    </div>
  `;

  return div;
}

function createGuestMessage(message) {
  let div = document.createElement("div");

  div.innerHTML = `
    <div class="row">
      <div class="col-xs-12">
        <div class="message-container guest-message">
          <p>${message.parts[0].payload.content}</p>
        </div>
      </div>
    </div>
  `;

  return div;
}

let testUser;

chatManager
  .connect()
  .then(currentUser => {
    console.log("Connected as user ", currentUser);
    testUser = currentUser;
    currentUser.subscribeToRoomMultipart({
      roomId: currentUser.rooms[0].id,
      hooks: {
        onMessage: message => {
          let newDiv = (currentUserID === message.senderId)
            ? createUserMessage(message)
            : createGuestMessage(message);

          document.getElementById("chat").appendChild(newDiv);

          var objDiv = document.getElementById("chat-body");
          console.log(objDiv);
          objDiv.scrollTop = objDiv.scrollHeight;
        }
      }
    });
  })
  .catch(error => {
    console.error("error:", error);
  });

  document.getElementById("user-input").addEventListener("keyup", function(event) {
    // Number 13 is the "Enter" key on the keyboard
    if (event.keyCode === 13) {
      // Cancel the default action, if needed
      event.preventDefault();

      let messageValue = document.getElementById("user-input").value;
      document.getElementById("user-input").value = "";

      testUser.sendSimpleMessage({
        text:  messageValue,
        roomId: testUser.rooms[0].id
      });
    }
  });
