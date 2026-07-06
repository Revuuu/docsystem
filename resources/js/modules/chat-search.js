import debounce from '../utils/debounce';

const ChatSearch = {

    init() {
        this.input =
            document.getElementById('chatSearchInput');

        this.rooms =
            document.querySelectorAll('.messenger-room-item');

        if (!this.input || !this.rooms.length) return;

        this.bindEvents();
    },

    bindEvents() {
        this.input.addEventListener(
            'input',
            debounce(() => {
                this.filterRooms();
            }, 200)
        );
    },

    filterRooms() {

        const keyword =
            this.input.value
                .trim()
                .toLowerCase();

        this.rooms.forEach(room => {

            const title =
                room.querySelector('.room-info strong')
                    ?.textContent
                    .trim()
                    .toLowerCase() || '';

            room.style.display =
                title.includes(keyword)
                    ? ''
                    : 'none';

        });

    }

};

export default ChatSearch;