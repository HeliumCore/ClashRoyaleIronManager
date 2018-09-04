$(document).ready(function () {
    $('#playersTable').on('click', 'tbody td', function () {
        $("body").css("cursor", "wait");
        window.location = $(this).closest('tr').find('.linkToPlayer').attr('href');
    });

    $('.clan-rank').each(function () {
        let pos = $(this).data('pos');
        if (pos === 1) {
            $(this).addClass("first-place");
        } else if (pos === 2) {
            $(this).addClass("second-place");
        } else if (pos === 3) {
            $(this).addClass("third-place");
        } else {
            $(this).addClass("last-place");
        }
    });

    let warHeadIndex = $('.warHeadIndex');

    if ($('#warBattleTh').is(':visible')) {
        console.log("test");
        warHeadIndex.removeClass("collectionRankIndex collectionPlayerIndex collectionCollectionIndex");
        let warClass = "";
        warHeadIndex.each(function () {
            switch ($(this).data('head')) {
                case 'rank':
                    warClass = "warRankIndex";
                    break;

                case 'player':
                    warClass = "warPlayerIndex";
                    break;

                case 'collection':
                    warClass = "warCollectionIndex";
                    break;

                default:
                    warClass = "";
                    break;
            }
            $(this).addClass(warClass)
        });
    }

    let searchInput = $('#warSearchInput');
    if (searchInput.css("float") === 'none') {
        searchInput.addClass("text-center");
    }

    searchInput.on("keyup paste", function () {
        let value = $(this).val().toLowerCase();
        const playerLine = $('.playerTr');
        if (value.length === 0) {
            playerLine.show();
            return;
        }

        playerLine.each(function () {
            if ($(this).find('.linkToPlayer').html().toLowerCase().indexOf(value) < 0)
                $(this).hide();
            else
                $(this).show();
        });
    });

    warHeadIndex.click(function () {
        let playerTrs = $('.playerTr');
        let ascSort = $(this).hasClass("ascSort");
        let descSort = $(this).hasClass("descSort");
        let rankHead = $('#rankHead');
        $('.warHeadIndex').removeClass("ascSort descSort");

        switch ($(this).data('head')) {
            case 'rank':
                if ((descSort === false && ascSort === false) || (ascSort === true)) {
                    playerTrs.sort(sortByRankDesc);
                    $(this).addClass("descSort");
                } else if (descSort === true) {
                    playerTrs.sort(sortByRankAsc);
                    $(this).addClass("ascSort");
                }
                break;

            case 'battle':
                if (descSort === false && ascSort === false) {
                    playerTrs.sort(sortByBattleDesc);
                    $(this).addClass("descSort");
                } else if (descSort === true) {
                    playerTrs.sort(sortByBattleAsc);
                    $(this).addClass("ascSort");
                } else if (ascSort === true) {
                    playerTrs.sort(sortByRankDesc);
                    rankHead.addClass("descSort");
                }
                break;

            case 'collection':
                if (descSort === false && ascSort === false) {
                    playerTrs.sort(sortByCollectionDesc);
                    $(this).addClass("descSort");
                } else if (descSort === true) {
                    playerTrs.sort(sortByCollectionAsc);
                    $(this).addClass("ascSort");
                } else if (ascSort === true) {
                    playerTrs.sort(sortByRankDesc);
                    rankHead.addClass("descSort");
                }
                break;

            default:
                break;
        }
        $('#js-war-body').html(playerTrs);
    });

    function sortByRankDesc(a, b) {
        let aRank = a.children[0].children[0].children[0].innerHTML;
        let bRank = b.children[0].children[0].children[0].innerHTML;
        return aRank - bRank;
    }

    function sortByRankAsc(a, b) {
        let aRank = a.children[0].children[0].children[0].innerHTML;
        let bRank = b.children[0].children[0].children[0].innerHTML;
        return bRank - aRank;
    }

    function sortByBattleDesc(a, b) {
        let battlePos = 0;
        for (let i = 0; i < a.children.length; i++) {
            if (a.children[i].className.indexOf("js-battle") > 0) {
                battlePos = i;
            }
        }
        let aBattle = a.children[battlePos].innerHTML.replace('Jouées<br>', '');
        let bBattle = b.children[battlePos].innerHTML.replace('Jouées<br>', '');
        return bBattle - aBattle;
    }

    function sortByBattleAsc(a, b) {
        let battlePos = 0;
        for (let i = 0; i < a.children.length; i++) {
            if (a.children[i].className.indexOf("js-battle") > 0) {
                battlePos = i;
            }
        }
        let aBattle = a.children[battlePos].innerHTML.replace('Jouées<br>', '');
        let bBattle = b.children[battlePos].innerHTML.replace('Jouées<br>', '');
        return aBattle - bBattle;
    }

    function sortByCollectionDesc(a, b) {
        let cardsPos = 0;
        for (let i = 0; i < a.children.length; i++) {
            if (a.children[i].className.indexOf("js-cards-earned") > 0) {
                cardsPos = i;
            }
        }
        let aCards = a.children[cardsPos].innerHTML.split('&nbsp;')[1];
        let bCards = b.children[cardsPos].innerHTML.split('&nbsp;')[1];

        return bCards - aCards;
    }

    function sortByCollectionAsc(a, b) {
        let cardsPos = 0;
        for (let i = 0; i < a.children.length; i++) {
            if (a.children[i].className.indexOf("js-cards-earned") > 0) {
                cardsPos = i;
            }
        }
        let aCards = a.children[cardsPos].innerHTML.split('&nbsp;')[1];
        let bCards = b.children[cardsPos].innerHTML.split('&nbsp;')[1];

        return aCards - bCards;
    }
});