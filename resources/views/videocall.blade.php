<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Call with ConnectyCube</title>
    <script src="https://cdn.jsdelivr.net/npm/connectycube@3.33.2/dist/connectycube.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            padding: 20px;
            background-color: #f5f5f5;
        }

        #videoContainer {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
        }

        video {
            width: 380px;
            height: 270px;
            background-color: black;
            border: 1px solid #ccc;
        }

        #callControls {
            text-align: center;
        }

        button {
            padding: 10px 20px;
            margin: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        #startCall {
            display: inline;
        }

        #endCall {
            display: none;
        }

        #userList {
            margin-top: 20px;
        }

        #userList select {
            padding: 10px;
            font-size: 16px;
        }

        .call-modal {
            display: none;
            position: fixed;
            top: 20%;
            left: 50%;
            transform: translate(-50%, -20%);
            background: white;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .call-modal.show {
            display: block;
        }

        .call-modal-header {
            font-size: 18px;
            margin-bottom: 10px;
        }

        .call-modal-footer {
            display: flex;
            justify-content: space-between;
        }

        .call-modal-button {
            padding: 10px 20px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>Video Calling System</h1>

    <div id="videoContainer">
        <div>
            <h3>Video Call</h3>
            <video id="remoteVideo" autoplay playsinline></video>
        </div>
    </div>

    <div id="callControls">
        <button id="startCall" class="startCall">Start Call</button>
        <button id="endCall">End Call</button>
    </div>

    <div id="userList">
        <h2>Select User to Call</h2>
        <select id="userSelect">
            <option value="" disabled selected>Select a user</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
            @endforeach
        </select>
    </div>

    <div id="call-modal-incoming" class="call-modal">
        <div class="call-modal-header">Incoming call from <span id="call-modal-initiator"></span></div>
        <div class="call-modal-footer">
            <button id="call-modal-accept" class="call-modal-button">Accept</button>
            <button id="call-modal-reject" class="call-modal-button">Reject</button>
        </div>
    </div>

    @vite(['resources/js/app.js'])

    <script language="JavaScript" type="module">
        const ConnectyCube = window.ConnectyCube;
        let currentSession = null;

        const CREDENTIALS = {
            appId: 7929,
            authKey: "LZLbYfF4wVpbgJb",
            authSecret: "2kRNSCHhVTTaxqj",
        };

        ConnectyCube.init(CREDENTIALS);

        const userCredentials = { userId: "12683887", password: "aqsa1234" };

        ConnectyCube.createSession().then((session) => {
            console.log("Session created:", session);
            return ConnectyCube.chat.connect(userCredentials);
        }).then((user) => {
            console.log("User logged in successfully", user);
        }).catch((error) => {
            console.error("Error during session creation or user login:", error);
        });

        function startCall(callType) {
            const selectedUserId = document.getElementById('userSelect').value;

            if (selectedUserId) {
                const opponentsIds = [parseInt(selectedUserId, 10)];
                const options = {};
                const session = ConnectyCube.videochat.createNewSession(opponentsIds, callType, options);
                currentSession = session;

                navigator.mediaDevices.getUserMedia({ video: true, audio: true })
                    .then((stream) => {
                        const localVideo = document.getElementById("remoteVideo");
                        if (localVideo) {
                            localVideo.srcObject = stream;
                            localVideo.play();
                        }

                        session.getUserMedia({ video: true, audio: true }).then((sessionStream) => {
                            session.call({});
                            // Show the end call button and hide the start call button
                            document.getElementById("startCall").style.display = "none";
                            document.getElementById("endCall").style.display = "inline";
                        }).catch((error) => console.error("Error getting user media for the session:", error));
                    })
                    .catch((error) => console.error("Error obtaining media stream:", error));
            } else {
                alert("Please select a user to start the video call");
            }
        }

        function endCall() {
    if (currentSession) {
        // Stop the call session
        currentSession.stop({});

        // Stop all tracks of the local media stream
        const localStream = document.getElementById("remoteVideo").srcObject;
        if (localStream) {
            localStream.getTracks().forEach(track => track.stop());
        }

        // Clear the media stream from the video element
        const remoteVideo = document.getElementById("remoteVideo");
        if (remoteVideo) {
            remoteVideo.srcObject = null;
        }

        // Set the current session to null
        currentSession = null;

        // Show the start call button and hide the end call button
        document.getElementById("startCall").style.display = "inline";
        document.getElementById("endCall").style.display = "none";
    }
}


        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById("startCall").addEventListener("click", function () {
                startCall(ConnectyCube.videochat.CallType.VIDEO);
            });

            document.getElementById("endCall").addEventListener("click", function () {
                endCall();
            });
        });

        ConnectyCube.videochat.onCallListener = (session, extension) => {
            console.log("onCallListener triggered");
            const initiatorName = extension.initiatorName || "Unknown";
            console.log("Incoming call from:", initiatorName);

            showIncomingCallModal(initiatorName);

            document.getElementById("call-modal-accept").onclick = () => {
                navigator.mediaDevices.getUserMedia({ video: true, audio: true })
                    .then((localStream) => {
                        session.attachMediaStream(localStream);
                        session.accept({});

                        session.onRemoteStreamListener = (remoteStream) => {
                            const remoteVideo = document.getElementById("remoteVideo");
                            remoteVideo.srcObject = remoteStream;
                            remoteVideo.play();
                        };
                    })
                    .catch((error) => console.error("Error accessing media devices.", error));

                hideIncomingCallModal();
            };

            document.getElementById("call-modal-reject").onclick = () => {
                session.reject({});
                hideIncomingCallModal();
            };
        };

        const showIncomingCallModal = (initiatorName) => {
            const $initiator = document.getElementById("call-modal-initiator");
            const $modal = document.getElementById("call-modal-incoming");

            $initiator.innerHTML = initiatorName;
            $modal.classList.add("show");
        };

        const hideIncomingCallModal = () => {
            document.getElementById("call-modal-incoming").classList.remove("show");
        };
    </script>

</body>
</html>
